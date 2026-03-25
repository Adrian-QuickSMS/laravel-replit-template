# Auto Top-Up Module — Implementation Handoff & Guardrails

## Branch & Merge Instructions

**Branch:** `claude/review-auto-topup-spec-QOpll`
**Commits:** 6 commits (feature + review fixes + critical fix + docs + Replit review fixes + VAT fix)
**Base:** `main`
**Files changed:** 30 files, ~3,600 lines added/modified

### Merge Command
```bash
git fetch origin claude/review-auto-topup-spec-QOpll
git checkout main
git merge origin/claude/review-auto-topup-spec-QOpll --no-ff
```

### Post-Merge Verification
```bash
php artisan migrate --force          # Must complete without errors
php -l app/Services/Billing/AutoTopUpService.php
php -l app/Services/Billing/BalanceService.php
php -l app/Http/Controllers/Api/V1/TopUpController.php
```

### CRITICAL: RLS Verification for CLI & Queue Workers

The `auto_topup_events` and `auto_topup_configs` tables have Row Level Security (RLS) enabled. RLS filters all rows when `app.current_tenant_id` is not set — which is the case for:
- Scheduled commands (e.g. `billing:expire-stale-auto-topups`)
- Queue workers processing `ProcessAutoTopUpJob` / `RetryAutoTopUpJob`
- The admin console queries

**This works correctly IF** the database user (`DB_USERNAME` in `.env`) has `BYPASSRLS` privilege, or is a superuser, or is the `svc_red` role. Verify this is the case:

```sql
-- Run as superuser to check:
SELECT rolname, rolbypassrls, rolsuper FROM pg_roles WHERE rolname = '<your DB_USERNAME>';
-- At least one of rolbypassrls or rolsuper must be TRUE
```

If the CLI/queue user does NOT bypass RLS, scheduled commands and jobs will silently find zero rows and do nothing. In that case, add `DB::statement("SET ROLE svc_red");` at the start of:
- `ExpireStaleAutoTopUpEvents::handle()`
- `ProcessAutoTopUpJob::handle()`
- `RetryAutoTopUpJob::handle()`

---

## Module Overview

The Auto Top-Up module allows **prepay** customer accounts to automatically replenish their balance when it falls below a configured threshold, using a Stripe payment method saved via Stripe Checkout (setup mode).

### Feature Location
- **Customer Portal:** `/payments/auto-topup` (Payments > Auto Top-Up)
- **Admin Console:** `/admin/billing/auto-topup` (Billing > Auto Top-Up)
- **API:** `/api/v1/topup/auto-topup/*`

### Core Flow
```
Message sent → BalanceService::deductForMessage()
    → Balance crosses below threshold (detected AFTER transaction commits)
    → AutoTopUpService::evaluateAutoTopUp()
        → Validates: enabled, not locked, prepay, daily limits, cooldown, no pending event
        → Acquires PG advisory lock (transaction-level)
        → Creates AutoTopUpEvent (status: pending)
        → Dispatches ProcessAutoTopUpJob
            → Creates Stripe PaymentIntent (off-session, confirmed)
            → Stripe webhook (payment_intent.succeeded) → credits balance
            → Stripe webhook (payment_intent.payment_failed) → logs failure, retries
```

---

## HARD RULES — DO NOT BREAK THESE

### 1. Auto Top-Up Evaluation MUST Run Outside the Balance Transaction

The `evaluateAutoTopUp()` call in `BalanceService::deductForMessage()` MUST execute AFTER the `DB::transaction()` closure completes. The transaction returns `$result` (an array), then the auto top-up check runs, then `$result['entry']` is returned.

```php
// CORRECT — this is how it must stay
$result = DB::transaction(function () use (...) {
    // ... balance deduction, ledger entry ...
    return ['entry' => $entry, 'previousEffective' => ..., 'newEffective' => ...];
});

// Auto top-up runs AFTER transaction — NEVER move this inside
try {
    if (!$isPostpay) {
        app(AutoTopUpService::class)->evaluateAutoTopUp(...);
    }
} catch (\Throwable $e) { ... }

return $result['entry'];
```

**Why:** Running inside the transaction holds the `account_balances` row lock for the duration of the auto top-up evaluation (multiple queries + advisory lock + job dispatch), which would degrade message sending performance for high-volume accounts.

**NEVER:** Change `$result = DB::transaction(...)` back to `return DB::transaction(...)`. This makes the auto top-up code unreachable.

### 2. Credit Is Added ONLY on Webhook Confirmation

Balance is credited in `handlePaymentSuccess()` which is called from the Stripe `payment_intent.succeeded` webhook. The synchronous PaymentIntent creation in `processAutoTopUp()` does NOT credit the balance, even if Stripe returns `status: succeeded` synchronously.

**NEVER:** Add balance crediting in `processAutoTopUp()` after the PaymentIntent creation. This would bypass the webhook idempotency flow and risk double-crediting.

### 3. Admin Lock Fields Are NOT in $fillable

The `AutoTopUpConfig` model's `$fillable` array deliberately excludes:
- `admin_locked`
- `admin_locked_reason`
- `admin_locked_at`
- `admin_locked_by`

These fields are set via `$config->forceFill([...])->save()` in `AutoTopUpService::adminDisable()` and `adminUnlock()` only.

**NEVER:** Add these fields back to `$fillable`. This would allow mass assignment from customer-facing request data to unlock admin-locked configs.

### 4. Stripe client_secret Is NOT Stored in the Database

The PaymentIntent `client_secret` is retrieved from Stripe's API on demand via `getClientSecretForEvent()`. It is NOT stored in the `metadata` jsonb column or anywhere else.

**NEVER:** Store `$paymentIntent->client_secret` in the database. It is a sensitive token that allows anyone to confirm a payment.

### 5. RLS Policies Must Remain on Both Tables

Both `auto_topup_configs` and `auto_topup_events` have RLS policies enforcing tenant isolation:
```sql
USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID)
```

**NEVER:** Disable RLS on these tables or remove the policies. Customer portal queries go through `portal_rw` role which enforces RLS. Admin queries go through `svc_red` which bypasses RLS.

### 6. Permission Middleware Is Required

All auto top-up API routes are wrapped in `permission:view_billing` middleware:
```php
Route::middleware(['permission:view_billing'])->group(function () {
    // all auto-topup routes
});
```

**NEVER:** Remove this middleware or move routes outside the permission group. This would allow any authenticated user (including `readonly` or `user` roles) to configure automatic payments.

### 7. VAT Follows Account Tax Status (Same Logic as InvoiceService)

VAT is determined **per-account** using the same rule as `InvoiceService::getVatRate()`:
- `account.vat_registered && !account.vat_reverse_charges` → **20% UK VAT**
- Otherwise → **0% (VAT not applicable)**

This means:
- VAT-registered UK businesses pay net + 20% VAT
- Non-VAT-registered customers pay net only (no VAT)
- Reverse-charge eligible customers (non-UK B2B) pay net only (no VAT)

The `topup_amount` field is always the NET amount. VAT is calculated in `triggerAutoTopUp()` via `getVatRateForAccount()`:
```php
$vatRate = $this->getVatRateForAccount($account); // 20.00 or 0.00
$vatAmount = bcmul($config->topup_amount, bcdiv($vatRate, '100', 6), 4);
$totalCharge = bcadd($config->topup_amount, $vatAmount, 4);
```

**NEVER:** Hardcode a VAT rate. Always use `getVatRateForAccount()` which mirrors `InvoiceService::getVatRate()`.

**NEVER:** Change `topup_amount` to be gross (VAT-inclusive). The existing invoice system expects net amounts.

### 8. Prepay Accounts Only

Auto top-up is restricted to `billing_type === 'prepay'`. This is enforced in:
- `evaluateAutoTopUp()` — checks account billing_type
- `updateAutoTopUp()` — returns 422 for non-prepay
- `setupPaymentMethod()` — returns 422 for non-prepay

**NEVER:** Remove these checks. Postpay accounts have credit limits and monthly invoicing; auto top-up makes no sense for them.

### 9. Daily Reset Is Midnight UTC

Daily count and value limits reset at midnight UTC. The `getDailyStats()` method uses:
```php
$today = now()->utc()->startOfDay();
```

**NEVER:** Change this to local time or remove the UTC conversion. Inconsistent day boundaries would allow customers to exceed daily limits.

### 10. Notifications: Failure OR Disabled, Never Both

When auto-disable triggers (3 consecutive failures), the shared `processFailureAndMaybeDisable()` method sends EITHER:
- `notifyAutoDisabled()` — if threshold met
- `notifyFailure()` — if threshold not met

**NEVER:** Call both notifications for the same event. Customers would receive confusing duplicate messages.

---

## FILE INVENTORY

### New Files (18)

| File | Purpose |
|------|---------|
| `database/migrations/2026_03_25_000001_enhance_auto_topup_system.php` | Schema: extends auto_topup_configs, creates auto_topup_events with RLS |
| `app/Models/Billing/AutoTopUpEvent.php` | Event log model (15 event types, 7 statuses) |
| `app/Services/Billing/AutoTopUpService.php` | Core engine: trigger, process, webhooks, setup, admin |
| `app/Services/Billing/AutoTopUpNotificationService.php` | In-app + email notification dispatch |
| `app/Jobs/ProcessAutoTopUpJob.php` | Queued job for Stripe PaymentIntent creation |
| `app/Jobs/RetryAutoTopUpJob.php` | Delayed retry job for failed payments |
| `app/Http/Controllers/Admin/AutoTopUpAdminController.php` | Admin: list, events, disable/lock, unlock |
| `app/Mail/AutoTopUpSuccessMail.php` | Success email Mailable |
| `app/Mail/AutoTopUpFailedMail.php` | Failure email Mailable |
| `app/Mail/AutoTopUpRequiresActionMail.php` | SCA required email Mailable |
| `app/Mail/AutoTopUpDisabledMail.php` | Auto-disabled email Mailable |
| `resources/views/emails/billing/auto-topup-success.blade.php` | Success email template |
| `resources/views/emails/billing/auto-topup-failed.blade.php` | Failure email template |
| `resources/views/emails/billing/auto-topup-requires-action.blade.php` | SCA email template |
| `resources/views/emails/billing/auto-topup-disabled.blade.php` | Disabled email template |
| `resources/views/quicksms/payments/auto-topup.blade.php` | Customer portal page |
| `resources/views/admin/billing/auto-topup.blade.php` | Admin management page |
| `app/Console/Commands/ExpireStaleAutoTopUpEvents.php` | Scheduled: expires stuck requires_action/pending events |

### Modified Files (12)

| File | What Changed |
|------|--------------|
| `app/Models/Billing/AutoTopUpConfig.php` | Full rewrite: new fields, helpers, tightened $fillable |
| `app/Models/Account.php` | Added `autoTopUpConfig()` HasOne relationship |
| `app/Services/Billing/BalanceService.php` | Threshold-crossing detection after transaction |
| `app/Services/Billing/StripeCheckoutService.php` | Delegates auto-topup to AutoTopUpService |
| `app/Http/Controllers/Api/V1/TopUpController.php` | Full CRUD, setup, events, validation |
| `app/Http/Controllers/Api/Webhooks/StripeWebhookController.php` | Routes auto-topup events + payment_method.detached |
| `app/Http/Controllers/QuickSMSController.php` | Portal page + SCA completion action |
| `routes/api_billing.php` | New permission-gated auto-topup route group |
| `routes/web.php` | Customer portal + admin page routes |
| `config/billing.php` | Extended auto_topup config block |
| `app/Providers/AppServiceProvider.php` | Scoped service registration |
| `app/Console/Kernel.php` | Added hourly `billing:expire-stale-auto-topups` schedule |

---

## EXTERNAL DEPENDENCIES

This module depends on tables created by **earlier migrations** (not included in this branch):

| Table | Created By | Purpose |
|-------|-----------|---------|
| `processed_stripe_events` | `2026_02_20_000007_create_payment_tables.php` | Webhook idempotency — prevents double-processing Stripe events |
| `payments` | `2026_02_20_000007_create_payment_tables.php` | Payment records (uses `stripe_auto_topup` payment method type) |
| `account_balances` | `2026_02_20_000003_create_ledger_tables.php` | Cached account balance with `lockForAccount()` |
| `ledger_entries` / `ledger_lines` | `2026_02_20_000003_create_ledger_tables.php` | Immutable double-entry ledger |
| `invoices` | `2026_02_20_000006_create_invoice_tables.php` | Invoice generation |
| `notifications` | Pre-existing | In-app notification delivery |
| `accounts.stripe_customer_id` | `2026_02_20_000001_add_billing_fields_to_accounts.php` | Stripe Customer ID (migration adds idempotently if missing) |

### Account Model Column Dependencies

The auto top-up trigger and VAT calculation depend on these columns existing on the `accounts` table:

| Column | Type | Used By | Impact If Missing |
|--------|------|---------|-------------------|
| `billing_type` | string | `evaluateAutoTopUp()` | Auto top-up silently never triggers (caught in try/catch) |
| `vat_registered` | boolean | `getVatRateForAccount()` | VAT calculation fails; auto top-up silently never triggers |
| `vat_reverse_charges` | boolean | `getVatRateForAccount()` | VAT calculation fails; auto top-up silently never triggers |
| `stripe_customer_id` | string | `setupPaymentMethod()` | Payment method setup fails with error |

These columns are created by `2026_02_20_000001_add_billing_fields_to_accounts.php`. If that migration has not run, the entire auto top-up feature will silently fail (no errors visible to customers, only in logs).

**IMPORTANT:** Verify these columns exist before deploying:
```sql
SELECT column_name FROM information_schema.columns
WHERE table_name = 'accounts' AND column_name IN ('billing_type', 'vat_registered', 'vat_reverse_charges', 'stripe_customer_id');
-- Must return all 4 rows
```

**IMPORTANT:** If `processed_stripe_events` does not exist, the Stripe webhook controller will throw 500 on every event.

---

## SCHEDULED TASKS

| Schedule | Command | Purpose |
|----------|---------|---------|
| Hourly | `billing:expire-stale-auto-topups` | Expires `requires_action` events after 24h (Stripe PI expiry), and cleans up orphaned pending/processing events |

---

## DATABASE SCHEMA

### Extended Table: `auto_topup_configs`

New columns added (all with `hasColumn` idempotency guards):

| Column | Type | Default | Purpose |
|--------|------|---------|---------|
| `daily_topup_cap` | decimal(10,4) | null | Max total value per day |
| `min_minutes_between_topups` | integer | 0 | Cooldown between triggers |
| `card_brand` | varchar(20) | null | e.g. visa, mastercard |
| `card_last4` | varchar(4) | null | Last 4 digits display |
| `card_exp_month` | smallint | null | Card expiry month |
| `card_exp_year` | smallint | null | Card expiry year |
| `notify_email_success` | boolean | true | Email on success |
| `notify_email_failure` | boolean | true | Email on failure |
| `notify_inapp_success` | boolean | true | In-app on success |
| `notify_inapp_failure` | boolean | true | In-app on failure |
| `notify_requires_action` | boolean | true | Notify on SCA required |
| `retry_attempts` | integer | 2 | Max retry count |
| `retry_delay_minutes` | integer | 10 | Delay between retries |
| `disable_after_consecutive_failures` | integer | 3 | Auto-disable threshold |
| `consecutive_failure_count` | integer | 0 | Current failure streak |
| `last_successful_topup_at` | timestamp | null | Last success time |
| `admin_locked` | boolean | false | Admin hard lock |
| `admin_locked_reason` | text | null | Lock reason (shown to customer) |
| `admin_locked_at` | timestamp | null | When locked |
| `admin_locked_by` | uuid | null | Admin who locked |
| `updated_by_user_id` | uuid | null | Last modifier |

### New Table: `auto_topup_events`

| Column | Type | Notes |
|--------|------|-------|
| `id` | uuid | Primary key |
| `account_id` | uuid | FK → accounts, indexed |
| `config_id` | uuid | FK → auto_topup_configs, nullable |
| `event_type` | varchar(40) | 15 possible types |
| `status` | varchar(20) | Default 'pending', 7 possible values |
| `trigger_balance` | decimal(12,4) | Balance at trigger time |
| `trigger_threshold` | decimal(10,4) | Config threshold at trigger |
| `topup_amount` | decimal(10,4) | Net amount |
| `vat_amount` | decimal(10,4) | VAT charged |
| `total_charge_amount` | decimal(10,4) | Net + VAT |
| `daily_count_before` | integer | Daily count before this event |
| `daily_value_before` | decimal(12,4) | Daily value before this event |
| `stripe_payment_intent_id` | varchar | Indexed for webhook lookup |
| `stripe_customer_id` | varchar | Stripe customer ref |
| `stripe_payment_method_id` | varchar | Stripe PM ref |
| `failure_code` | varchar(100) | Stripe error code |
| `failure_message` | text | Human-readable error |
| `requires_action_url` | text | SCA completion URL |
| `idempotency_key` | varchar | **Unique constraint** |
| `retry_of_event_id` | uuid | Self-FK for retry chain |
| `retry_count` | integer | Default 0 |
| `metadata` | jsonb | Flexible extra data |
| `created_at` | timestamp | Auto-set |
| `processed_at` | timestamp | When processing began |
| `completed_at` | timestamp | When resolved |

**RLS:** Enabled with tenant isolation policy.
**Grants:** `portal_rw` gets SELECT/INSERT/UPDATE; `portal_ro` gets SELECT.

---

## API ENDPOINTS

All under `/api/v1/topup/auto-topup/*`, middleware: `auth:customer` + `permission:view_billing` + `throttle:120,1`.

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/topup/auto-topup` | Get config + daily stats |
| PUT | `/topup/auto-topup` | Update config (enable/disable/settings) |
| POST | `/topup/auto-topup/disable` | Quick disable |
| POST | `/topup/auto-topup/setup-payment-method` | Create Stripe Checkout setup session |
| POST | `/topup/auto-topup/payment-method/remove` | Detach PM + disable |
| GET | `/topup/auto-topup/events` | List events (max 100) |

### Admin Endpoints (RED zone, `svc_red` role)

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/admin/billing/auto-topup` | Admin page |
| GET | `/admin/api/billing/auto-topup` | List all configs (paginated, searchable) |
| GET | `/admin/api/billing/auto-topup/{id}/events` | Account event history |
| POST | `/admin/api/billing/auto-topup/{id}/disable` | Disable + hard lock |
| POST | `/admin/api/billing/auto-topup/{id}/unlock` | Remove lock |

---

## SAFETY CONTROLS

| Control | Implementation |
|---------|---------------|
| Anti-duplicate | PG advisory lock (transaction-level) + pending event check inside lock |
| Daily count limit | `max_topups_per_day` (default 3, admin-configurable max from config) |
| Daily value cap | `daily_topup_cap` (validated ≤ config max_daily_cap) |
| Cooldown | `min_minutes_between_topups` checked against `last_triggered_at` |
| Auto-disable | After `disable_after_consecutive_failures` (default 3) consecutive failures |
| Hard decline no-retry | `card_not_supported`, `expired_card`, `fraudulent`, `lost_card`, `stolen_card` |
| Admin lock | Customer cannot re-enable; admin must unlock |
| Idempotency | Unique `idempotency_key` on events + Stripe idempotency key on PI creation |
| Webhook idempotency | `processed_stripe_events` table prevents double-processing |
| XSS prevention | `esc()` HTML escape helper on all innerHTML dynamic content |
| Permission gate | `view_billing` permission required for all customer endpoints |
| Prepay-only guard | Three separate checks (trigger, update, setup) |

---

## CONFIG VALUES

In `config/billing.php` → `auto_topup`:

| Key | Default | Purpose |
|-----|---------|---------|
| `max_per_day` | 3 | Maximum top-ups per day (env: `AUTO_TOPUP_MAX_PER_DAY`) |
| `min_amount` | 5.00 | Minimum top-up amount (£) |
| `max_amount` | 50,000.00 | Maximum single top-up (£) |
| `max_daily_cap` | 100,000.00 | Maximum daily cap setting (£) |
| `default_retry_attempts` | 2 | Default retries per failure |
| `default_retry_delay_minutes` | 10 | Default retry delay |
| `default_consecutive_failure_limit` | 3 | Default auto-disable threshold |

VAT rate is NOT in this config block. It is determined per-account from `account.vat_registered` and `account.vat_reverse_charges` via `AutoTopUpService::getVatRateForAccount()`, using the same logic as `InvoiceService::getVatRate()`. The actual rates come from `config('billing.vat.uk_rate')` (20.00) and `config('billing.vat.default_rate')` (0.00).

---

## STRIPE INTEGRATION

| Stripe Feature | Usage |
|----------------|-------|
| Customer | Created on first payment method setup; ID stored on `accounts.stripe_customer_id` |
| Checkout Session (setup mode) | Used to save payment method; redirects customer to Stripe-hosted page |
| SetupIntent | Created by Checkout Session; extracts PaymentMethod on completion |
| PaymentMethod | Stored as `stripe_payment_method_id`; card metadata (brand, last4, expiry) cached |
| PaymentIntent (off-session) | Created with `confirm: true` for automatic charges |
| Webhook: `checkout.session.completed` | Handles setup mode completion → saves payment method |
| Webhook: `payment_intent.succeeded` | Credits balance, creates payment record + invoice |
| Webhook: `payment_intent.payment_failed` | Logs failure, increments count, schedules retry |
| Webhook: `payment_method.detached` | Disables auto top-up if PM matches |

---

## WHAT NOT TO TOUCH

These files were modified as part of this module. Do not revert or significantly restructure them without understanding the auto top-up integration points:

1. **`BalanceService::deductForMessage()`** — The `$result = DB::transaction(...)` pattern and post-transaction auto top-up check are critical. See Hard Rule #1.

2. **`StripeCheckoutService::handleCheckoutCompleted()`** — Now delegates setup-mode sessions to `AutoTopUpService`. The `if ($type === 'auto_topup_setup' || ...)` check must remain.

3. **`StripeCheckoutService::handlePaymentIntentSucceeded()`** — Now delegates `auto_topup` type to `AutoTopUpService`. The `if ($type === 'auto_topup')` delegation must remain.

4. **`StripeWebhookController::handle()`** — Now routes `payment_method.detached` events. The match arm must remain.

5. **`AppServiceProvider::register()`** — `AutoTopUpService` and `AutoTopUpNotificationService` registered as `scoped()` (not `singleton`). This is for Octane safety.

---

## TESTING CHECKLIST

Before considering this feature complete, verify:

### Pre-deploy Verification
- [ ] `php artisan migrate --force` completes without errors on fresh and existing DB
- [ ] Verify `processed_stripe_events` table exists (dependency from earlier migration)
- [ ] Verify Account model columns exist: `billing_type`, `vat_registered`, `vat_reverse_charges`, `stripe_customer_id` (see SQL check in External Dependencies)
- [ ] Verify `DB_USERNAME` role has `BYPASSRLS` (see RLS Verification above)
- [ ] Queue workers are running (jobs won't process without them)
- [ ] Scheduler is running (`billing:expire-stale-auto-topups` is hourly)

### Customer Portal
- [ ] `/payments/auto-topup` loads for prepay account owner
- [ ] Shows "only available for prepay" for postpay accounts
- [ ] "Add Payment Method" redirects to Stripe Checkout and returns with card saved
- [ ] Enable auto top-up with valid settings → confirmation modal → save succeeds
- [ ] Admin-locked accounts show locked banner, form disabled
- [ ] VAT preview shows 20% for VAT-registered accounts
- [ ] VAT preview shows "VAT not applicable" for non-VAT-registered or reverse-charge accounts
- [ ] `view_billing` permission required (non-billing users get 403)
- [ ] Activity log shows events with escaped content (no XSS)

### Trigger & Payment Flow
- [ ] Simulate balance deduction below threshold → event created, job dispatched
- [ ] Stripe `payment_intent.succeeded` webhook → balance credited, payment + invoice created
- [ ] Stripe `payment_intent.payment_failed` webhook → failure logged, notification sent
- [ ] 3 consecutive failures → auto-disable, single "disabled" notification (not "disabled" + "failed")
- [ ] Daily count limit prevents excess top-ups
- [ ] Cooldown timer prevents rapid-fire charges
- [ ] Failed job marks event as failed (not left orphaned in pending)

### Admin Console
- [ ] Admin can view all accounts, filter by status
- [ ] Admin can disable/lock with reason → customer sees locked banner
- [ ] Admin can unlock → customer can re-enable
- [ ] Event history modal shows Stripe PI IDs for support

### VAT Correctness
- [ ] VAT-registered UK business (not reverse charge): Stripe charges net + 20% VAT
- [ ] Non-VAT-registered customer: Stripe charges net only (0% VAT)
- [ ] Reverse-charge eligible customer: Stripe charges net only (0% VAT)
- [ ] Invoice created by `createTopUpInvoice()` matches the VAT applied to the Stripe charge

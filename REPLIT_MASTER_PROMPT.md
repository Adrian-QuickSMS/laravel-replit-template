# MASTER REPLIT PROMPT: Pull, Merge & Protect — 7-Status Lifecycle + 6 Security Fixes

## READ THIS ENTIRE FILE BEFORE DOING ANYTHING. DO NOT SKIM.

This is the **single authoritative prompt** for merging all Claude branch work into the Replit environment. It replaces all previous REPLIT_PULL_AND_MERGE*.md files. Follow it step-by-step, in order, with zero improvisation.

---

## What You Are Merging

| Commit | Summary |
|--------|---------|
| `1478246` | 7-status account lifecycle with test mode enforcement and fraud screening |
| `d1a6f19` | Guardrail prompt for test mode, fraud screening & account status lifecycle |
| `d2f1e18` | Mitigate all Replit-identified risks for 7-status account lifecycle |
| `ff186bc` | **Fix 6 security/performance risks** (race conditions, admin notifications, campaign bypass, migration rollback, number normalisation) |
| `fdc4e38` | **Fix transaction scoping**, scheduled campaign re-validation, deadlock prevention |
| `5373af9` | **Fix 7-status naming mismatches** across frontend, services, and seeders |

### The 6 Security/Performance Fixes (Detail)

**Fix A — Race condition in test credit deduction:**
`Account::deductTestCredits()` now uses `SELECT...FOR UPDATE` with FIFO deduction across credit rows. `TestModeEnforcementService::enforce()` atomically deducts after all checks pass, preventing concurrent requests from exceeding the 100-fragment limit.

**Fix B — transitionTo() race condition:**
`transitionTo()` is wrapped in `DB::transaction()` with `lockForUpdate()`. Concurrent status transitions (e.g. admin approve + auto-approve firing simultaneously) cannot both read the same old status and both succeed. Caller-level transactions in `FraudScreeningService` (`adminApprove`, `adminReject`, `approveAccount`) ensure flags updates roll back if the status transition fails.

**Fix C — notifyAdmin() was a TODO stub:**
Implemented using existing `AdminNotification` model with severity levels, deep links, and structured meta. Fraud screening events (auto-reject, pending review, scoring unavailable) now surface in admin dashboard.

**Fix D — CampaignService bypassed TestModeEnforcementService:**
Added `TestModeEnforcementService` as a dependency in `CampaignService`. `validateForSend()` now enforces test mode constraints (credit limits, sender ID restrictions, recipient validation) on the campaign send path. `processScheduled()` re-validates at dispatch time so test rules cannot be bypassed by scheduling before a status change.

**Fix E — Migration down() lost test/live distinction:**
Rollback now restores `account_type='trial'` for `test_standard`/`test_dynamic` rows so the old system can differentiate trial accounts from live accounts.

**Fix F — normalizeNumber() didn't handle 0044 prefix:**
Added handling for international dialling prefix (`00xx`) before the UK local format (`0x`) check in `PhoneNumberUtils`.

### Additional Fixes (Commits fdc4e38 + 5373af9)

- **Deadlock prevention:** Documented lock ordering contract — `transitionTo()` locks `accounts` rows, `deductTestCredits()` locks `account_credits` rows. Never reverse this order.
- **InvoiceApiController:** Added status display mapping (raw + display status) for 7-status model.
- **HubSpotInvoiceService:** Added inbound status mapping (legacy `'active'`/`'trial'` → new constants).
- **ReconciliationService:** Replaced hardcoded `'closed'` string with `Account::STATUS_CLOSED`.
- **SystemAccountSeeder:** Uses `Account::STATUS_ACTIVE_STANDARD` constant.
- **billing-services.js:** Added `AccountStatusUtil` normaliser (7-status → display category).
- **billing.blade.php:** Updated status display functions to use normaliser.
- **Mock data:** Updated to use new status values (`active_standard`, `test_standard`, etc.).

---

## The 7-Status Account Lifecycle (Test Mode Feature)

```
signup
  │
  ▼
pending_verification ──► FraudScreeningService scores signup
  │                        │
  │   score ≤ 30           │  score 31-70         │  score > 70
  │   (auto-approve)       │  (manual review)     │  (auto-reject)
  ▼                        ▼                      ▼
test_standard         admin reviews           stays pending
  │                   ┌─ approve ──► test_standard or test_dynamic
  │                   └─ reject  ──► stays pending (flagged)
  │
  ├──► test_dynamic  (admin can switch between standard/dynamic in test)
  │
  │  Payment verified + admin approves
  ▼
active_standard ◄──► active_dynamic  (admin can switch in live too)
  │
  ├──► suspended  (can return to any operational status)
  └──► closed     (terminal — no transitions out)
```

| Status | Mode | Can Send? | Credits |
|--------|------|-----------|---------|
| `pending_verification` | — | No | No |
| `test_standard` | Test | Yes (restricted recipients, disclaimer prepended, registered SenderIDs only) | Test credits (100 fragments) |
| `test_dynamic` | Test | Yes (any valid E.164, no disclaimer, any format-valid SenderID) | Test credits (100 fragments) |
| `active_standard` | Live | Yes (registered SenderIDs only) | Real balance |
| `active_dynamic` | Live | Yes (any format-valid SenderID) | Real balance |
| `suspended` | — | No | No |
| `closed` | — | No | No |

---

## Step 1: Pull and Merge

```bash
# Fetch the branch
git fetch origin claude/quicksms-security-performance-dr8sw

# Merge into your working branch
git merge origin/claude/quicksms-security-performance-dr8sw --no-edit

# Clear all caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# Run migrations
php artisan migrate --force
```

**Conflict resolution rule:** If there are merge conflicts, **keep the incoming (Claude branch) version** for ALL backend files (models, services, controllers, migrations, config, routes, seeders, JS service files). Preserve your local UI work in Blade templates only where it does not conflict with status badge mappings or lifecycle logic.

---

## Step 2: Verify the Merge

Run each of these. All must pass.

```bash
# 1. Migrations ran cleanly
php artisan migrate:status | grep -E "2026_03_06"

# 2. Routes registered
php artisan route:list --path=api/campaigns | head -20
php artisan route:list --path=api/numbers | head -20
php artisan route:list --path=api/message-templates | head -10

# 3. Models instantiate
php artisan tinker --execute="new App\Models\Campaign(); echo 'OK';"
php artisan tinker --execute="new App\Models\MessageTemplate(); echo 'OK';"
php artisan tinker --execute="new App\Models\PurchasedNumber(); echo 'OK';"
php artisan tinker --execute="new App\Models\Billing\CampaignEstimateSnapshot(); echo 'OK';"

# 4. Account model has 7-status constants
php artisan tinker --execute="echo App\Models\Account::STATUS_TEST_STANDARD . ' ' . App\Models\Account::STATUS_ACTIVE_DYNAMIC;"

# 5. Config loads
php artisan tinker --execute="echo config('billing.vat_rate');"

# 6. No syntax errors
php artisan route:clear && php artisan config:clear && php artisan view:clear

# 7. Server starts
php artisan serve --host=0.0.0.0 --port=5000
```

---

## Step 3: File Manifest — What You Now Have

### 3A. New Migrations (run in order — DO NOT MODIFY)

| Migration | Purpose |
|-----------|---------|
| `2026_02_24_000001` through `2026_03_02_000001` | Core tables: message_templates, campaigns, campaign_recipients, media_library, numbers module (5 tables), rcs_assets, campaign_estimate_snapshots |
| `2026_03_06_000001_update_account_status_enum_for_test_modes.php` | Adds 4 ENUM values to account_status, migrates data, rebuilds `account_safe_view`, adds partial indexes |
| `2026_03_06_000002_add_test_mode_fields_to_account_settings.php` | Adds `approved_test_numbers` JSONB column |
| `2026_03_06_000003_update_sp_create_account_for_7status.php` | Updates stored procedure for pending_verification flow |
| `2026_03_06_000004_add_enum_rollback_safety_net.php` | Installs rollback_account_status_enum() stored function (ops_admin only) |

### 3B. Key New/Modified Backend Files

| File | What It Does |
|------|-------------|
| `app/Services/TestModeEnforcementService.php` | **Single source of truth** for test mode rules: credit check, recipient validation, SenderID enforcement, disclaimer prepending, fragment calculation |
| `app/Services/FraudScreeningService.php` | Third-party scoring + fraud screening with 3-tier outcome (auto-approve / manual review / auto-reject) and admin notification |
| `app/Models/Account.php` | 7 status constants, grouping arrays, transition map, `transitionTo()`, `deductTestCredits()` (with `SELECT...FOR UPDATE`), 12+ helper methods |
| `app/Services/Campaign/CampaignService.php` | Full campaign lifecycle with test mode enforcement on send + scheduled dispatch |
| `app/Services/Campaign/PhoneNumberUtils.php` | MSISDN normalisation including `0044` prefix handling |
| `app/Http/Controllers/Api/InvoiceApiController.php` | Status display mapping for 7-status model |
| `app/Services/HubSpotInvoiceService.php` | Inbound legacy status mapping |
| `app/Services/Billing/ReconciliationService.php` | Uses `Account::STATUS_CLOSED` constant |
| `database/seeders/SystemAccountSeeder.php` | Uses `Account::STATUS_ACTIVE_STANDARD` |
| `public/js/quicksms-account-lifecycle.js` | Frontend 7-status model with helpers and transitions |
| `public/js/billing-services.js` | `AccountStatusUtil` normaliser for display |
| `resources/views/admin/accounts/billing.blade.php` | Updated status functions |
| `resources/views/admin/overview.blade.php` | All 7 statuses with labels and badge colours |

---

## GUARDRAILS — ABSOLUTE RULES THAT MUST NEVER BE VIOLATED

These are not suggestions. They are hard constraints. Violating any of them **will** create data corruption, security holes, or silent billing bugs.

### G1: Status transitions MUST use `transitionTo()`

```php
// ✅ CORRECT — validates transition, logs change, uses lockForUpdate()
$account->transitionTo(Account::STATUS_ACTIVE_STANDARD);

// ❌ WRONG — bypasses validation, creates invalid state, no locking
$account->update(['account_status' => 'active_standard']);
$account->account_status = 'test_dynamic';
$account->save();
```

`transitionTo()` validates against `STATUS_TRANSITIONS` map and uses database locking. Direct updates bypass all of this. **There are zero exceptions for application code.**

### G2: Test mode checks MUST go through TestModeEnforcementService

```php
// ✅ CORRECT — single source of truth
$result = app(TestModeEnforcementService::class)->enforce($account, $recipient, $senderId, $content);
if (!$result->allowed) {
    return response()->json(['error' => $result->reason], 422);
}
$finalContent = $result->finalContent;

// ❌ WRONG — scattering checks creates drift
if ($account->isTestStandard()) {
    // manually check approved numbers
    // manually prepend disclaimer
}
```

### G3: The disclaimer is exactly 67 characters — do not change it

```
QuickSMS TEST message. If unexpected, do not trust links or numbers.
```
- Constant: `Account::TEST_DISCLAIMER` (string) and `Account::TEST_DISCLAIMER_LENGTH` (67)
- Prepended with space separator: `{disclaimer} {customer_content}` = 68 + content length
- Fragment calculation in `TestModeEnforcementService::calculateFragments()` accounts for this
- **If you change the text, you MUST update `TEST_DISCLAIMER_LENGTH` to match**

### G4: ALWAYS use Account model constants — NEVER hardcode status strings

```php
// ✅ CORRECT
if ($account->isTestStandard()) { ... }
->where('account_status', Account::STATUS_TEST_STANDARD)
Account::TEST_STATUSES       // ['test_standard', 'test_dynamic']
Account::LIVE_STATUSES        // ['active_standard', 'active_dynamic']
Account::OPERATIONAL_STATUSES // all 4 above

// ❌ WRONG
if ($account->account_status === 'test_standard') { ... }
->where('account_status', 'active_standard')
```

### G5: `scopeActive()` means ALL operational accounts — not just "active"

`Account::active()` returns `test_standard + test_dynamic + active_standard + active_dynamic`.

- Need only live accounts? → `Account::liveMode()` or `Account::LIVE_STATUSES`
- Need only test accounts? → `Account::testMode()` or `Account::TEST_STATUSES`
- **Do NOT change `scopeActive()` back to a single-status check**

### G6: Test credits and real balance are two separate systems — NEVER mix them

| | Test Credits | Real Balance |
|---|---|---|
| Used by | `test_standard`, `test_dynamic` | `active_standard`, `active_dynamic` |
| Check | `$account->getAvailableCredits()` | `BillingPreflightService::runPreflight()` |
| Deducted by | `TestModeEnforcementService` → `Account::deductTestCredits()` | `BillingPreflightService` |
| Default | 100 fragments (`Account::DEFAULT_TEST_CREDITS`) | £0.00 (must top up) |

Test accounts must never hit billing preflight. Live accounts must never check test credits.

### G7: Dynamic accounts bypass SenderID registration

`SenderIdEnforcementService` checks `$account->isDynamic()`. If true, the SenderID only needs format validation (alphanumeric 3-11 chars for alpha, or valid E.164 for numeric). **Do not add registration checks for dynamic accounts anywhere else.**

### G8: Fraud screening has three outcomes — handle all three

```
Score ≤ 30  → auto-approve  → test_standard (with AdminNotification)
Score 31-70 → manual review  → stays pending_verification, admin notified
Score > 70  → auto-reject    → stays pending_verification, flagged, admin notified
API down    → manual review  → fail-safe, NEVER auto-approve on failure
```

Thresholds are constants in `FraudScreeningService` and in `config/services.php`. Do not change without business approval.

### G9: Lock ordering — accounts THEN account_credits

`transitionTo()` locks `accounts` rows. `deductTestCredits()` locks `account_credits` rows. **Never acquire these locks in reverse order.** This contract prevents deadlocks between concurrent transitions and credit deductions.

### G10: The `account_safe_view` MUST include all operational statuses

```sql
WHERE status IN ('test_standard', 'test_dynamic', 'active_standard', 'active_dynamic', 'suspended')
```
If you ever add a status (you shouldn't), this view must be updated via migration.

---

## ANTI-DRIFT RULES — What You Must NOT Do

### ❌ AD1: Do NOT create new account statuses

If you think you need a new status, you almost certainly don't. If you truly do, you must update ALL of:
- PostgreSQL ENUM type (migration)
- `Account::STATUS_*` constants
- Grouping arrays (`TEST_STATUSES`, `LIVE_STATUSES`, `OPERATIONAL_STATUSES`, etc.)
- `STATUS_TRANSITIONS` map
- `account_safe_view` (migration)
- Admin overview blade (label + badge mapping)
- `AdminController` dashboard counts
- `TestModeEnforcementService`
- `FraudScreeningService`
- `quicksms-account-lifecycle.js`
- `billing-services.js` AccountStatusUtil

### ❌ AD2: Do NOT create new migrations that modify the account_status ENUM

The ENUM is managed by `2026_03_06_000001`. If you touch it, the rollback safety net in `2026_03_06_000004` becomes invalid.

### ❌ AD3: Do NOT fork TestModeEnforcementService into per-channel versions

When RCS test mode is needed, add an `enforceRcs()` method to the existing service. One service, one source of truth.

### ❌ AD4: Do NOT inline test mode logic in controllers or other services

All test mode logic lives in `TestModeEnforcementService`. If you find yourself writing `if ($account->isTestMode()) { ... }` with send/credit/recipient logic, you are doing it wrong. Call the service.

### ❌ AD5: Do NOT move fraud screening to a queue job without updating the full flow

The current flow is synchronous. Moving to async requires: pending UI state, retry/dead-letter handling, admin visibility for queued screenings. All must ship together.

### ❌ AD6: Do NOT re-introduce the 10K credit cap

Removed intentionally. Admin is trusted to set appropriate limits. `Account::DEFAULT_TEST_CREDITS` (100) is the default for new test accounts.

### ❌ AD7: Do NOT add `approved_test_numbers` validation to the Account model

Validation happens in `TestModeEnforcementService::isApprovedTestNumber()`. Do not add Eloquent casts, mutators, or model-level validation — it creates a second validation path.

### ❌ AD8: Do NOT use `withoutGlobalScopes()` in customer-facing code

This bypasses tenant isolation (RLS). Only admin controllers and system services may use it.

### ❌ AD9: Do NOT suppress invalid transition exceptions

```php
// ❌ WRONG — hiding bugs
try {
    $account->transitionTo($newStatus);
} catch (\InvalidArgumentException $e) {
    // ignore
}
```
If a transition is invalid, the business logic is wrong. Fix it, don't suppress it.

### ❌ AD10: Do NOT duplicate Account helper methods

The model has 12+ helpers: `isTestMode()`, `isLiveMode()`, `isTestStandard()`, `isTestDynamic()`, `isActiveStandard()`, `isActiveDynamic()`, `isStandard()`, `isDynamic()`, `requiresTestDisclaimer()`, `requiresApprovedTestNumbers()`, `usesTestCredits()`, `isPendingVerification()`. Check existing helpers before adding new ones.

### ❌ AD11: Do NOT modify pulled files

Do not rename, refactor, reorganise, "clean up", convert to MySQL syntax, simplify, merge classes, or add "improvements" to any file listed in this document. They are final.

### ❌ AD12: Do NOT move routes between files

Routes in `web.php` stay in `web.php`. Routes in `api_billing.php` stay in `api_billing.php`. Contact/invoice/purchase APIs stay in `web.php` under `customer.auth` (not `api.php` — the `api` middleware group does not carry session cookies).

### ❌ AD13: Do NOT create mock data or hardcode test values

All data comes from the database. No seed data in controllers, no hardcoded arrays of sample accounts/campaigns/templates.

### ❌ AD14: Do NOT add new packages or dependencies

Everything uses existing Laravel + PostgreSQL features. If you think you need a new package, you are overcomplicating the solution.

### ❌ AD15: Do NOT modify `setup.sh`, `config/billing.php`, or billing services

`setup.sh` is configured for Replit's PostgreSQL environment. Billing config values (VAT rate, currency, limits) are set. `BalanceService`, `PricingEngine`, `LedgerService`, `InvoiceService` are called by new services but must not be modified.

---

## Step 4: What Replit Can Safely Work On AFTER Merging

After merge is verified, Replit is free to work on **UI/frontend only**:

| Page | What To Build | Backend Already Exists |
|------|--------------|----------------------|
| Send Message page | Wire form UI to campaign APIs | `CampaignApiController` (CRUD, prepare, estimate, send, schedule) |
| Numbers management | Blade page calling numbers endpoints | `NumberApiController` (library, pool, purchase, assign, auto-reply) |
| RCS content creator | Card/carousel editor in send wizard | `RcsAssetController` (upload, edit, finalize) |
| Campaign history | List campaigns with status/delivery data | `GET /api/campaigns` |
| Admin account management | Display all 7 statuses with transition buttons | Account model helpers + `transitionTo()` |

**Build Blade + jQuery + Bootstrap 5 frontend that calls existing API endpoints. Do NOT create new backend routes, controllers, services, or migrations.**

### Frontend status display reference:

```javascript
// Use AccountStatusUtil from billing-services.js
const display = AccountStatusUtil.normalize(account.account_status);
// Returns: { category: 'test'|'active'|'pending'|'suspended'|'closed', label: 'Test (Standard)', badgeClass: 'badge-info' }
```

```php
// In Blade templates, use the Account model helpers:
@if($account->isTestMode())
    <span class="badge badge-info">Test Mode</span>
@elseif($account->isLiveMode())
    <span class="badge badge-success">Live</span>
@endif
```

---

## Step 5: Emergency Reference

### Valid status transitions (from Account::STATUS_TRANSITIONS):

```
pending_verification → test_standard, test_dynamic
test_standard        → test_dynamic, active_standard, suspended, closed
test_dynamic         → test_standard, active_dynamic, suspended, closed
active_standard      → active_dynamic, suspended, closed
active_dynamic       → active_standard, suspended, closed
suspended            → test_standard, test_dynamic, active_standard, active_dynamic, closed
closed               → (nothing — terminal)
```

### Account model constants:

```php
Account::STATUS_PENDING_VERIFICATION  // 'pending_verification'
Account::STATUS_TEST_STANDARD         // 'test_standard'
Account::STATUS_TEST_DYNAMIC          // 'test_dynamic'
Account::STATUS_ACTIVE_STANDARD       // 'active_standard'
Account::STATUS_ACTIVE_DYNAMIC        // 'active_dynamic'
Account::STATUS_SUSPENDED             // 'suspended'
Account::STATUS_CLOSED                // 'closed'

Account::TEST_STATUSES                // ['test_standard', 'test_dynamic']
Account::LIVE_STATUSES                // ['active_standard', 'active_dynamic']
Account::STANDARD_STATUSES            // ['test_standard', 'active_standard']
Account::DYNAMIC_STATUSES             // ['test_dynamic', 'active_dynamic']
Account::OPERATIONAL_STATUSES         // all 4 operational statuses

Account::DEFAULT_TEST_CREDITS         // 100
Account::TEST_DISCLAIMER              // 'QuickSMS TEST message...' (67 chars)
Account::TEST_DISCLAIMER_LENGTH       // 67
```

### Fragment calculation:

```
GSM-7:  1 fragment = 160 chars, multi = 153 chars each
UCS-2:  1 fragment = 70 chars,  multi = 67 chars each
Test Standard adds: 68 chars (67 disclaimer + 1 space) before customer content
```

---

## Summary — What This Merge Delivers

1. **7-status account lifecycle** replacing the old 2-status (active/trial) model
2. **Test mode enforcement** with credit limits, recipient restrictions, SenderID validation, and content disclaimers
3. **Fraud screening** with auto-approve/manual-review/auto-reject tiers and admin notifications
4. **Race condition protection** on credit deduction and status transitions via database-level locking
5. **Campaign send path enforcement** — test mode rules apply to both immediate and scheduled sends
6. **Full frontend sync** — JS lifecycle, billing services, admin blades, and seeders all use 7-status model
7. **Migration safety** — rollback function, data mapping for all edge cases, partial indexes for performance

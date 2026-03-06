# Replit Risk Mitigations — 7-Status Account Lifecycle

This document addresses every risk identified in the Replit code review, documents what was already handled, and details the fixes applied.

---

## "What Needs Improving" — Status & Mitigations

### 1. Migration data transformation edge cases
**Risk:** The migration only maps `trial + active` and `non-trial + active`. What about `trial + suspended`? `system + active`?

**Mitigation (APPLIED):**
- Updated `2026_03_06_000001_update_account_status_enum_for_test_modes.php`:
  - `active + trial` → `test_standard` (explicit match)
  - `active + (anything else)` → `active_standard` (catch-all for system, prepay, postpay, any unexpected value)
  - `suspended` → `suspended` (unchanged — not touched by migration)
  - `closed` → `closed` (unchanged)
  - `pending_verification` → `pending_verification` (unchanged)
- Added PL/pgSQL safety assertion that raises a WARNING if any accounts still have `status = 'active'` after migration.
- The migration matrix is now **exhaustive** — every combination is handled.

### 2. No rollback strategy for ENUM changes
**Risk:** PostgreSQL ENUM changes are irreversible. If migration fails midway, the database is in a broken state.

**Mitigation (APPLIED):**
- Created `2026_03_06_000004_add_enum_rollback_safety_net.php` containing a `rollback_account_status_enum()` stored function that:
  1. Validates no accounts use new status values (safety check)
  2. Drops dependent views/indexes
  3. Converts column to TEXT → drops old ENUM → recreates 4-value ENUM → converts back
  4. Rebuilds indexes and views
- Function is restricted to `ops_admin` role only
- Must be called manually after running the `down()` data migration
- **Deployment recommendation:** Always take a database backup before running enum migrations. Use `pg_dump` with `--section=pre-data --section=data` for quick restore.

### 3. account_credits table doesn't exist yet
**Status: ALREADY EXISTS** ✅

The table exists at `database/migrations/2026_02_10_000008_create_account_credits_table_postgres.php` with:
- `credit_type` ENUM: signup_promo, mobile_verification, referral, purchased, bonus, compensation
- Full RLS policies for tenant isolation
- `AccountCredit` model at `app/Models/AccountCredit.php` with `getAvailableCreditsAttribute()`
- `Account::getAvailableCredits()` method at line 669

### 4. account_flags table not listed in manifest
**Status: ALREADY EXISTS** ✅

The table exists at `database/migrations/2026_02_10_100003_create_account_flags_table_postgres.php` with:
- `fraud_risk_level` ENUM: low, medium, high, critical
- `payment_status` ENUM: current, overdue, suspended, collections
- RED SIDE only — never exposed to portal
- `AccountFlags` model at `app/Models/AccountFlags.php`
- `FraudScreeningService::recordScore()` creates/updates flags

### 5. account_settings.approved_test_numbers dependency
**Status: SAFE** ✅

The `account_settings` table exists at `database/migrations/2026_02_10_000007_create_account_settings_table_postgres.php`. Migration `2026_03_06_000002_add_test_mode_fields_to_account_settings.php` adds the JSONB column as an ALTER TABLE, which is safe.

### 6. No frontend integration guidance
**Risk:** Frontend JS files use old 5-state model (TEST, LIVE_SELF_SERVICE, etc.)

**Mitigation (APPLIED):**
- **`public/js/quicksms-account-lifecycle.js`** fully rewritten to use 7-status model:
  - `STATES` now uses lowercase values matching backend: `pending_verification`, `test_standard`, `test_dynamic`, `active_standard`, `active_dynamic`, `suspended`, `closed`
  - Added `TEST_STATUSES`, `LIVE_STATUSES`, `OPERATIONAL_STATUSES` group arrays
  - Added status-specific helpers: `isTestStandard()`, `isTestDynamic()`, `isActiveStandard()`, `isActiveDynamic()`, `isOperational()`, `isStandard()`, `isDynamic()`
  - Added `requiresTestDisclaimer()`, `requiresApprovedNumbers()`
  - `VALID_TRANSITIONS` now mirrors `Account::STATUS_TRANSITIONS` exactly
  - `activateAccount()` preserves standard/dynamic mode (test_standard → active_standard, test_dynamic → active_dynamic)
  - Admin override targets `active_dynamic` by default
  - `init()` accepts both `status` (new) and `lifecycle_state` (legacy) fields
- **`resources/views/admin/assets/sender-id-detail.blade.php`** — updated account status badge to recognize operational statuses and distinguish test/live
- **`resources/views/admin/assets/rcs-agent-detail.blade.php`** — same fix
- **`resources/views/quicksms/reporting/invoices.blade.php`** — updated status display with 7-status labels and appropriate icons/classes

### 7. scopeActive() semantic change is dangerous
**Risk:** `scopeActive()` now returns test + live accounts, which could leak test data into billing queries or reports.

**Mitigation (ALREADY SAFE):**
- `scopeActive()` returns `OPERATIONAL_STATUSES` — this is intentional and correct for the use case "accounts that can send messages"
- The codebase already uses the correct scopes for specific contexts:
  - `Account::LIVE_STATUSES` for billing/admin counts (see `AdminController::accountsOverview()` line 226)
  - `Account::TEST_STATUSES` for test mode counts (line 227)
  - `scopeLiveMode()` for queries that need only live accounts
  - `scopeTestMode()` for queries that need only test accounts
- **All existing callers of `Account::active()` have been verified** — they are either:
  - Already updated to use `LIVE_STATUSES` where appropriate (admin dashboard)
  - Or correctly expect "any operational account" behavior (message sending gates)
- **Other models** (User, AdminUser, Contact, Supplier, etc.) have their own independent `scopeActive()` methods that check their own `status` column — these are NOT affected by the account_status ENUM change.

### 8. Test credits handling on transition
**Risk:** What happens to remaining test credits when going from test to live?

**Mitigation (APPLIED):**
- Updated `AccountObserver::updated()` to detect test→live transitions via status constants:
  ```php
  if (in_array($oldStatus, Account::TEST_STATUSES) && in_array($newStatus, Account::LIVE_STATUSES)) {
      $this->expirePromotionalCredits($account);
  }
  ```
- This voids all promotional/test credits (signup_promo, mobile_verification, referral) immediately
- The existing `expirePromotionalCredits()` method marks credits with `expired_at` timestamp and sets `credits_remaining = 0`
- Purchased credits (if any) are preserved
- This is logged for audit purposes

### 9. Stripe Radar / fraud scoring configuration
**Status: ALREADY CONFIGURED** ✅

`config/services.php` contains:
```php
'fraud_scoring' => [
    'url' => env('FRAUD_SCORING_API_URL'),
    'api_key' => env('FRAUD_SCORING_API_KEY'),
    'auto_approve_threshold' => (int) env('FRAUD_SCORING_AUTO_APPROVE', 30),
    'manual_review_threshold' => (int) env('FRAUD_SCORING_MANUAL_REVIEW', 70),
]
```
- The provider is intentionally abstracted — the `FraudScreeningService` works with any REST scoring API
- Stripe Radar is used separately for payment fraud (via webhook), not signup fraud
- Fail-safe: if scoring API is unavailable, defaults to manual review (score 50)

---

## "What Will Break If You Deploy This" — Status & Mitigations

### 1. Every existing query using old account_status values will break
**Risk:** Code using `'active'`, `'trial'` as status values will find no matching rows.

**Status: SAFE** ✅

Verified through comprehensive codebase audit:
- **Account model** — uses constants (`Account::STATUS_*`) throughout, not string literals
- **AdminController** — uses `Account::LIVE_STATUSES`, `Account::TEST_STATUSES` for counts
- **AccountActivationController** — uses `Account::STATUS_ACTIVE_STANDARD` / `Account::STATUS_ACTIVE_DYNAMIC`
- **FraudScreeningService** — uses `Account::STATUS_TEST_STANDARD` / `Account::TEST_STATUSES`
- **All `scopeActive()` calls on OTHER models** (User, AdminUser, Contact, Supplier, etc.) — these check their own tables' status columns which use separate ENUMs (`user_status`, etc.) and are NOT affected

**Additional fix (APPLIED):**
- `AdminController` line 596: changed fallback from `'active'` to `Account::STATUS_PENDING_VERIFICATION`
- `HubSpotInvoiceService` line 339: changed default from `'active'` to `'active_standard'`

### 2. scopeActive() semantic change
**Status: SAFE** — See item 7 above.

### 3. RLS policies may need updating
**Status: SAFE** ✅

RLS policies on the `accounts` table do NOT reference status values. They use tenant ID matching:
```sql
CREATE POLICY accounts_isolation ON accounts
    USING (id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid)
```
Service roles (`svc_red`, `ops_admin`, `postgres`) have full bypass. No status-based filtering in any RLS policy.

### 4. account_safe_view needs testing
**Status: ALREADY UPDATED** ✅

Migration `2026_03_06_000001` rebuilds the view with:
```sql
WHERE status IN ('test_standard', 'test_dynamic', 'active_standard', 'active_dynamic', 'suspended')
```
Excludes `pending_verification` (not yet approved) and `closed` (archived).

### 5. Session-based auth flow
**Status: SAFE** ✅

`SetTenantContext` middleware does NOT check `account_status`. It only:
1. Gets `tenant_id` from authenticated user or session
2. Sets PostgreSQL session variable `app.current_tenant_id`
3. RLS policies use tenant_id, not status

The stored procedure `sp_authenticate_user` checks `user_status != 'active'` (the `user_status` ENUM), not `account_status`. These are separate ENUMs.

---

## Additional Fixes Applied

### Stored Procedure: sp_create_account
**Created:** `2026_03_06_000003_update_sp_create_account_for_7status.php`

The original `sp_create_account` inserted accounts with `'active'::account_status`. New accounts should enter `pending_verification` to go through fraud screening. Updated the stored procedure accordingly.

---

## Deployment Checklist

1. **Pre-deployment:**
   - [ ] Take full database backup (`pg_dump`)
   - [ ] Verify no active transactions are running during migration
   - [ ] Ensure `FRAUD_SCORING_API_URL` and `FRAUD_SCORING_API_KEY` env vars are set

2. **Migration order (all idempotent):**
   - `2026_03_06_000001` — Add ENUM values + migrate data + rebuild view + indexes
   - `2026_03_06_000002` — Add `approved_test_numbers` to account_settings
   - `2026_03_06_000003` — Update sp_create_account for pending_verification flow
   - `2026_03_06_000004` — Install rollback safety net function

3. **Post-deployment verification:**
   - [ ] `SELECT status, COUNT(*) FROM accounts GROUP BY status` — no rows with `'active'`
   - [ ] `SELECT * FROM account_safe_view LIMIT 5` — returns correct columns
   - [ ] Test new signup → account enters `pending_verification`
   - [ ] Test fraud screening auto-approve → account transitions to `test_standard`
   - [ ] Admin dashboard counts are correct
   - [ ] Portal users can still log in (session auth unaffected)

4. **Emergency rollback (if needed):**
   - Run `down()` on migrations 000004 → 000003 → 000002 → 000001
   - If ENUM cleanup needed: `SELECT rollback_account_status_enum()` (ops_admin only)

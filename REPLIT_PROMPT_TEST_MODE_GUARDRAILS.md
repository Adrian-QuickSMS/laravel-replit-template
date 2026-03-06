# Replit Prompt: Test Mode, Fraud Screening & Account Status — Guardrails & Anti-Drift

## Scope — Read This First

This prompt describes a **completed implementation** of the QuickSMS test mode / live mode account lifecycle. It exists as a guardrail document — use it to validate behaviour, catch regressions, and prevent drift when working on related code.

**This is NOT an implementation prompt.** All 16 files are already written. Do not re-create, duplicate, or rewrite any of them.

---

## Pull the branch first

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git checkout claude/quicksms-security-performance-dr8sw
php artisan route:clear && php artisan config:clear
```

---

## Architecture Overview — The 7-Status Account Lifecycle

Every QuickSMS account follows this lifecycle:

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

### The 7 Statuses

| Status | Mode | Type | Can Send? | Credits? |
|--------|------|------|-----------|----------|
| `pending_verification` | — | — | No | No |
| `test_standard` | Test | Standard | Yes (restricted) | Test credits (100 fragments) |
| `test_dynamic` | Test | Dynamic | Yes (less restricted) | Test credits (100 fragments) |
| `active_standard` | Live | Standard | Yes | Real balance |
| `active_dynamic` | Live | Dynamic | Yes | Real balance |
| `suspended` | — | — | No | No |
| `closed` | — | — | No | No |

### Standard vs Dynamic — The Key Difference

| Feature | Standard | Dynamic |
|---------|----------|---------|
| SenderID | Must be pre-registered and approved via SenderID Tool | Any SenderID passing format validation |
| Recipients (test mode) | Approved test numbers only (`account_settings.approved_test_numbers`) | Any valid E.164 mobile number |
| Disclaimer (test mode) | Prepended: `QuickSMS TEST message. If unexpected, do not trust links or numbers.` (67 chars) | No disclaimer |

---

## File Manifest — What Was Built

### NEW files (6):

| # | File | Purpose |
|---|------|---------|
| 1 | `database/migrations/2026_03_06_000001_update_account_status_enum_for_test_modes.php` | Adds 4 ENUM values, migrates data, updates `account_safe_view`, adds partial indexes |
| 2 | `database/migrations/2026_03_06_000002_add_test_mode_fields_to_account_settings.php` | Adds `approved_test_numbers` JSONB column |
| 3 | `app/Services/TestModeEnforcementService.php` | Rules engine for test mode restrictions (credits, recipients, senderIDs, content) |
| 4 | `app/Services/FraudScreeningService.php` | Third-party scoring + Stripe Radar fraud screening |

### MODIFIED files (10):

| # | File | What Changed |
|---|------|-------------|
| 5 | `app/Models/Account.php` | 7 status constants, grouping arrays, transition map, 12+ helper methods, updated scopes |
| 6 | `app/Http/Controllers/AccountActivationController.php` | test→live preserves standard/dynamic mode |
| 7 | `app/Http/Controllers/AdminController.php` | Dashboard counts use new status constants |
| 8 | `app/Http/Controllers/Admin/BillingAdminController.php` | Removed 10K credit cap, added test-mode guard |
| 9 | `app/Services/SenderIdEnforcementService.php` | Dynamic accounts bypass SenderID registration requirement |
| 10 | `app/Services/Billing/XeroService.php` | Uses `transitionTo()` for status changes |
| 11 | `app/Services/Billing/ReconciliationService.php` | Uses `transitionTo()` for status changes |
| 12 | `resources/views/admin/overview.blade.php` | Maps all 7 statuses to display labels and badge colours |
| 13 | `config/services.php` | Fraud scoring API configuration block |

---

## GUARDRAILS — Rules That Must Never Be Violated

### G1: Status Transitions Must Use `transitionTo()`

**NEVER** do this:
```php
$account->update(['account_status' => 'active_standard']);
$account->account_status = 'test_dynamic';
$account->save();
```

**ALWAYS** do this:
```php
$account->transitionTo(Account::STATUS_ACTIVE_STANDARD);
```

`transitionTo()` validates against the transition map and logs the change. Direct updates bypass validation and can create invalid state (e.g., `closed` → `active_standard`).

**Exception:** The migrations themselves and `FraudScreeningService::approveAccount()` may set status directly during initial account creation flow because `pending_verification` → `test_standard` is already validated internally.

### G2: Test Mode Checks Must Go Through TestModeEnforcementService

**NEVER** inline test mode logic like this:
```php
if ($account->isTestStandard()) {
    // manually check approved numbers
    // manually prepend disclaimer
}
```

**ALWAYS** call the service:
```php
$result = app(TestModeEnforcementService::class)->enforce($account, $recipient, $senderId, $content);
if (!$result->allowed) {
    return response()->json(['error' => $result->reason], 422);
}
$finalContent = $result->finalContent;
```

The service is the **single source of truth** for test mode rules. Scattering checks across controllers creates drift.

### G3: The Disclaimer Is Exactly 67 Characters — Do Not Change It

```
QuickSMS TEST message. If unexpected, do not trust links or numbers.
```

- Constant: `Account::TEST_DISCLAIMER` (string) and `Account::TEST_DISCLAIMER_LENGTH` (67)
- It is prepended with a space separator: `{disclaimer} {customer_content}`
- Total = 68 + customer content length
- This affects fragment calculation — `TestModeEnforcementService::calculateFragments()` accounts for it
- **If you change the disclaimer text, you must update `TEST_DISCLAIMER_LENGTH` to match**
- The UI fragment counter uses `getEffectiveContent()` to show accurate counts including disclaimer

### G4: Fragment Calculation Uses GSM-7 / UCS-2 Detection

```
GSM-7:  1 fragment = 160 chars, multi-fragment = 153 chars each
UCS-2:  1 fragment = 70 chars,  multi-fragment = 67 chars each
```

- `TestModeEnforcementService::calculateFragments()` handles this
- `isGsm7()` checks against the GSM 03.38 basic character set + extension table
- Any character outside GSM-7 (emoji, accented chars like ñ, Chinese/Arabic/etc.) triggers UCS-2
- **Do not create a separate fragment calculator** — reuse the one in `TestModeEnforcementService`

### G5: Account Model Constants Are the Source of Truth for Status Values

**NEVER** hardcode status strings:
```php
// BAD
if ($account->account_status === 'test_standard') { ... }
->where('account_status', 'active_standard')
```

**ALWAYS** use constants:
```php
// GOOD
if ($account->isTestStandard()) { ... }
->where('account_status', Account::STATUS_TEST_STANDARD)
```

**Status grouping arrays — use these, don't create your own:**
```php
Account::TEST_STATUSES       // ['test_standard', 'test_dynamic']
Account::LIVE_STATUSES        // ['active_standard', 'active_dynamic']
Account::STANDARD_STATUSES    // ['test_standard', 'active_standard']
Account::DYNAMIC_STATUSES     // ['test_dynamic', 'active_dynamic']
Account::OPERATIONAL_STATUSES // all 4 above — means "can send messages"
```

### G6: `scopeActive()` Now Means "All Operational" — Not Just `active`

The old meaning of `scopeActive()` was `WHERE account_status = 'active'`. It now means `WHERE account_status IN ('test_standard', 'test_dynamic', 'active_standard', 'active_dynamic')`.

If you need **only live accounts**, use `scopeLiveMode()`.
If you need **only test accounts**, use `scopeTestMode()`.

**Do not change `scopeActive()` back to a single status check.** Every existing call to `Account::active()` must continue to return all operational accounts.

### G7: Dynamic Accounts Bypass SenderID Registration

`SenderIdEnforcementService` already checks `$account->isDynamic()`. If true, the SenderID does not need to exist in the `sender_ids` table — it only needs to pass format validation (alphanumeric, 3-11 chars for alpha, or valid E.164 for numeric).

**Do not add SenderID registration checks for dynamic accounts elsewhere.** The service handles it.

### G8: Test Credits vs Real Balance — Two Different Systems

| | Test Credits | Real Balance |
|---|---|---|
| Used by | `test_standard`, `test_dynamic` | `active_standard`, `active_dynamic` |
| Source table | `account_credits` | `account_balances` (via billing engine) |
| Default amount | 100 fragments | £0.00 (must top up) |
| Check method | `$account->getAvailableCredits()` | `BillingPreflightService::runPreflight()` |
| Deducted by | `TestModeEnforcementService::checkTestCredits()` | `BillingPreflightService` |

**Do not mix these.** Test mode accounts should never hit the billing preflight for balance checks. Live mode accounts should never check test credits.

### G9: Fraud Screening Has Three Outcomes — All Three Must Be Handled

```
Score ≤ 30  → auto-approve  → account transitions to test_standard/test_dynamic
Score 31-70 → manual review  → account stays pending_verification, admin notified
Score > 70  → auto-reject    → account stays pending_verification, flagged
```

**Fail-safe:** If the scoring API is down or returns an error, the account is flagged for **manual review** (not auto-approved, not auto-rejected). This is intentional — do not change it.

The thresholds are constants in `FraudScreeningService`:
```php
const SCORE_AUTO_APPROVE = 30;
const SCORE_MANUAL_REVIEW = 70;
```

### G10: The `account_safe_view` Must Include All Operational Statuses

The view filters: `WHERE account_status IN ('test_standard', 'test_dynamic', 'active_standard', 'active_dynamic', 'suspended')`.

If you add a new status, you **must** update this view. The view is used by admin dashboard queries and reporting.

---

## ANTI-DRIFT RULES — What Not To Do

### AD1: Do NOT Create New Account Statuses Without Updating Everything

If you ever need a new status (you probably don't), you must update ALL of:
- [ ] The PostgreSQL ENUM type (requires migration)
- [ ] `Account::STATUS_*` constants
- [ ] The grouping arrays (`TEST_STATUSES`, `LIVE_STATUSES`, etc.)
- [ ] `STATUS_TRANSITIONS` map
- [ ] `account_safe_view` (via migration)
- [ ] Admin overview blade (label + badge mapping)
- [ ] `AdminController` dashboard counts
- [ ] `TestModeEnforcementService` (if the status affects test mode rules)
- [ ] `FraudScreeningService` (if the status is a screening outcome)

**This is why you should NOT create new statuses lightly.**

### AD2: Do NOT Fork TestModeEnforcementService Into Per-Channel Versions

The service handles SMS rules. When RCS test mode rules are needed, **extend** the existing service with an `enforceRcs()` method — do not create `RcsTestModeEnforcementService`. One service, one source of truth.

### AD3: Do NOT Move Fraud Screening Into a Queue Job Without Updating the Flow

The current flow is synchronous (called during signup). If you move it to a queue job:
- The account will be in `pending_verification` until the job runs
- The UI must show a "verification in progress" state
- The job must handle retries and dead-letter scenarios
- The admin must be able to see queued-for-screening accounts

All of these changes must happen together. Do not just `dispatch(new ScreenSignupJob($account))` without the UI and admin support.

### AD4: Do NOT Remove the 10K Credit Cap Removal From BillingAdminController

The old 10K credit cap was removed intentionally. Test accounts get 100 credits by default, and admin can add more without a hard cap. Do not re-introduce a credit cap — the admin is trusted to set appropriate limits.

### AD5: Do NOT Add `approved_test_numbers` Validation to the Account Model

The `approved_test_numbers` field is JSONB and lives in `account_settings`. Validation of individual numbers happens in `TestModeEnforcementService::isApprovedTestNumber()`. Do not add Eloquent cast rules, mutators, or model-level validation for this field — it would create a second validation path.

### AD6: Do NOT Change the Status Transition Map Without a Migration

The transition map in `Account::STATUS_TRANSITIONS` must match what the business logic expects. If you change allowed transitions, you must:
1. Document the business reason
2. Update any UI that shows available transitions to admin
3. Verify no existing accounts are in an invalid state for the new map

### AD7: Do NOT Skip `canTransitionTo()` by Catching the Exception

```php
// BAD — Silently swallowing invalid transitions
try {
    $account->transitionTo($newStatus);
} catch (\InvalidArgumentException $e) {
    // ignore
}
```

If a transition is invalid, it means the business logic is wrong. Fix the logic, don't suppress the error.

### AD8: Do NOT Duplicate Status Helper Methods

The Account model already has 12+ helpers: `isTestMode()`, `isLiveMode()`, `isTestStandard()`, `isTestDynamic()`, `isActiveStandard()`, `isActiveDynamic()`, `isStandard()`, `isDynamic()`, `requiresTestDisclaimer()`, `requiresApprovedTestNumbers()`, `usesTestCredits()`, `isPendingVerification()`.

Before adding a new helper, check if an existing one or a combination already covers your case. For example:
- "Can this account send messages?" → `isActive()` (checks OPERATIONAL_STATUSES)
- "Is this a test account?" → `isTestMode()`
- "Does this account need the disclaimer?" → `requiresTestDisclaimer()` (only test_standard)

### AD9: Do NOT Use `withoutGlobalScopes()` to Query Accounts in Customer-Facing Code

The Account model has a tenant scope. Using `withoutGlobalScopes()` in portal/customer controllers bypasses tenant isolation. Only admin controllers and system services (fraud screening, billing reconciliation) should use `withoutGlobalScopes()`.

### AD10: Do NOT Hardcode "100" as the Test Credit Amount Anywhere Except Account Model

```php
Account::DEFAULT_TEST_CREDITS  // = 100
```

Use the constant. If the default changes, it should only need to change in one place.

---

## Integration Points — Where Test Mode Connects to Existing Code

### Message Sending (Campaign + Single Message)

```
User clicks Send
    │
    ▼
Controller receives request
    │
    ├── account.isTestMode()?
    │       │
    │       YES → TestModeEnforcementService::enforce()
    │              │
    │              ├── checkTestCredits() — enough fragments?
    │              ├── checkRecipient() — approved number? (standard) / valid E.164? (dynamic)
    │              ├── checkSenderId() — registered? (standard) / format-valid? (dynamic)
    │              └── applyContentRules() — prepend disclaimer (standard only)
    │              │
    │              └── TestModeResult { allowed, finalContent, fragments, disclaimerApplied }
    │
    └── account.isLiveMode()?
            │
            YES → BillingPreflightService::runPreflight()
                   │
                   └── Normal billing flow (balance check, reserve funds, etc.)
```

### Account Activation (test → live)

```
AccountActivationController
    │
    ├── account is test_standard → transitionTo(active_standard)
    ├── account is test_dynamic  → transitionTo(active_dynamic)
    │
    └── Preserves standard/dynamic mode across the test→live boundary
```

### Admin Dashboard

```
AdminController::overview()
    │
    ├── Account::where('account_status', Account::STATUS_TEST_STANDARD)->count()
    ├── Account::where('account_status', Account::STATUS_TEST_DYNAMIC)->count()
    ├── Account::where('account_status', Account::STATUS_ACTIVE_STANDARD)->count()
    ├── Account::where('account_status', Account::STATUS_ACTIVE_DYNAMIC)->count()
    ├── Account::where('account_status', Account::STATUS_PENDING_VERIFICATION)->count()
    ├── Account::where('account_status', Account::STATUS_SUSPENDED)->count()
    └── Account::where('account_status', Account::STATUS_CLOSED)->count()
```

### Admin Overview Blade — Status Badge Mapping

```
test_standard        → "Test (Standard)"    → badge-info
test_dynamic         → "Test (Dynamic)"     → badge-primary
active_standard      → "Active (Standard)"  → badge-success
active_dynamic       → "Active (Dynamic)"   → badge-success
pending_verification → "Pending"            → badge-warning
suspended            → "Suspended"          → badge-danger
closed               → "Closed"             → badge-secondary
```

---

## Testing Checklist

### Status Transitions
- [ ] `pending_verification` → `test_standard` works via `transitionTo()`
- [ ] `pending_verification` → `test_dynamic` works via `transitionTo()`
- [ ] `test_standard` → `active_standard` works (preserves standard mode)
- [ ] `test_dynamic` → `active_dynamic` works (preserves dynamic mode)
- [ ] `closed` → any status throws `\InvalidArgumentException`
- [ ] Direct `$account->update(['account_status' => '...'])` is never used in application code

### Test Mode Enforcement
- [ ] Test Standard: message to non-approved number → rejected
- [ ] Test Standard: message to approved number → allowed, disclaimer prepended
- [ ] Test Standard: fragment count includes disclaimer (68 + content length)
- [ ] Test Dynamic: message to any valid E.164 → allowed, no disclaimer
- [ ] Test Dynamic: message to invalid number → rejected
- [ ] Test Standard: unregistered SenderID → rejected
- [ ] Test Dynamic: any format-valid SenderID → allowed
- [ ] Both: 0 test credits remaining → rejected
- [ ] `getEffectiveContent()` returns correct fragment count for UI

### Fraud Screening
- [ ] Score ≤ 30 → account auto-transitions to `test_standard`
- [ ] Score 31-70 → account stays `pending_verification`, admin notified
- [ ] Score > 70 → account stays `pending_verification`, flagged as rejected
- [ ] API timeout → account flagged for manual review (fail-safe)
- [ ] `adminApprove()` transitions account and records in `account_flags`
- [ ] `adminReject()` keeps account pending and records reason

### Scopes & Queries
- [ ] `Account::active()->get()` returns test_standard + test_dynamic + active_standard + active_dynamic
- [ ] `Account::testMode()->get()` returns test_standard + test_dynamic only
- [ ] `Account::liveMode()->get()` returns active_standard + active_dynamic only
- [ ] `Account::standard()->get()` returns test_standard + active_standard
- [ ] `Account::dynamic()->get()` returns test_dynamic + active_dynamic

### Admin UI
- [ ] Admin overview shows all 7 status counts
- [ ] Each status has correct badge colour and label
- [ ] Admin can transition accounts between allowed statuses

### Migration
- [ ] Migration runs cleanly on fresh database
- [ ] Migration handles existing data: trial+active → test_standard, non-trial+active → active_standard
- [ ] `account_safe_view` includes all 4 operational statuses + suspended
- [ ] Partial indexes exist for test_mode and live_mode queries

---

## What NOT to Change

- **Do not modify** `TestModeEnforcementService.php` rules without updating this guardrail doc
- **Do not modify** `FraudScreeningService.php` thresholds without business approval
- **Do not modify** the status transition map without updating all downstream code
- **Do not modify** the disclaimer text without updating the length constant
- **Do not modify** the ENUM migration after it has been run in production
- **Do not create** new services that duplicate test mode logic
- **Do not create** new account statuses without following AD1 checklist
- **Do not create** separate test mode handling for RCS (extend the existing service instead)
- **Do not remove** any of the Account model helper methods — they are used across the codebase
- **Do not remove** the partial indexes — they are critical for admin dashboard query performance

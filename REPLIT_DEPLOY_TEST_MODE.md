# REPLIT DEPLOY PROMPT: Pull & Deploy Test Mode — All Changes, All Fixes

## READ THIS ENTIRE PROMPT BEFORE DOING ANYTHING. DO NOT SKIM.

This is the **single deployment prompt** for pulling the complete test mode feature (7-status account lifecycle, fraud screening, security fixes, and delivery enforcement) from the Claude branch into your Replit environment. Follow every step in order. Zero improvisation.

---

## What You Are Deploying

**Branch:** `claude/quicksms-security-performance-dr8sw`
**Total commits:** 619 commits ahead of main (includes merge of 264 main commits)

### Feature Summary

| Feature | Description |
|---------|-------------|
| **7-status account lifecycle** | Replaces old 2-status (active/trial) model with: `pending_verification`, `test_standard`, `test_dynamic`, `active_standard`, `active_dynamic`, `suspended`, `closed` |
| **Test mode enforcement** | Credit limits (100 fragments), recipient restrictions, SenderID validation, disclaimer prepending — all via `TestModeEnforcementService` |
| **Fraud screening** | Auto-approve (score ≤30), manual review (31-70), auto-reject (>70) with admin notifications |
| **Race condition fixes** | `SELECT...FOR UPDATE` on credit deduction, `lockForUpdate()` on status transitions |
| **Campaign enforcement** | Test mode rules enforced on both immediate and scheduled campaign sends |
| **Delivery-level enforcement** | Per-message test mode checks in `DeliveryService` without double-billing |
| **Frontend sync** | JS lifecycle, billing services, admin blades, seeders — all use 7-status model |

### Security Fixes Included

| Fix | Issue | Resolution |
|-----|-------|------------|
| A | Race condition in test credit deduction | `SELECT...FOR UPDATE` with FIFO deduction |
| B | `transitionTo()` race condition | `DB::transaction()` + `lockForUpdate()` |
| C | `notifyAdmin()` was a TODO stub | Implemented via `AdminNotification` model |
| D | `CampaignService` bypassed test enforcement | Added `TestModeEnforcementService` dependency |
| E | Migration `down()` lost test/live distinction | Restores `account_type='trial'` on rollback |
| F | `normalizeNumber()` missed `0044` prefix | Added international dialling prefix handling |
| G | Double-billing in DeliveryService | New `validateForDelivery()` method (checks without deducting) |
| H | Disclaimer stacking on retry | In-memory only until `markSent()->save()` |

---

## ANTI-DRIFT GUARDRAILS — VIOLATION = STOP AND ASK

### DO NOT MODIFY
- Do NOT refactor, rename, reorganize, or "improve" any pulled file
- Do NOT change variable names, method names, class names, or formatting
- Do NOT add comments, docblocks, type hints, or annotations to existing code
- Do NOT change any database migration files
- Do NOT change any model files (especially `Account.php` — it has 7 status constants, transition map, locking logic)
- Do NOT modify `TestModeEnforcementService.php` or `FraudScreeningService.php`
- Do NOT modify `DeliveryService.php` test mode enforcement
- Do NOT modify `Kernel.php`, `SetTenantContext.php`, or `AuthController.php`
- Do NOT modify `composer.json`, `package.json`, `setup.sh`, `.replit`, or `replit.nix`

### DO NOT CREATE
- Do NOT create new files not required for deployment
- Do NOT create new database migrations
- Do NOT create new services that duplicate test mode logic
- Do NOT create per-channel test mode services (extend existing service instead)

### DO NOT DELETE
- Do NOT delete any existing files, routes, views, controllers, or models
- Do NOT delete any migration files

### DO NOT RUN
- Do NOT run `php artisan migrate:fresh` — this destroys all data
- Do NOT run `php artisan migrate:rollback`
- Do NOT run `composer update` — only `composer install`
- Do NOT run `npm update` — only `npm install`

### SPECIFIC DRIFT PATTERNS TO REJECT
1. **Adding UUID mutators** (`bin2hex`, `hex2bin`) — PostgreSQL uses native 36-char UUID strings
2. **Adding password hashing to User model** — hashed ONCE in controller only
3. **Removing `SetTenantContext` from Kernel.php** — MUST remain in `api` middleware group
4. **Adding NULL-context bypass to RLS policies** — NULL = zero rows, not all rows
5. **Replacing stored procedure calls with Eloquent** — `sp_create_account()` and `sp_authenticate_user()` are mandatory
6. **Changing `scopeActive()` to single-status check** — it returns ALL operational statuses
7. **Hardcoding status strings** instead of using `Account::STATUS_*` constants
8. **Inlining test mode logic** instead of calling `TestModeEnforcementService`
9. **Mixing test credits with real balance** — two separate systems
10. **Suppressing `transitionTo()` exceptions** — if transition is invalid, fix the logic

---

## Step 1: Pull the Branch

```bash
# Fetch the branch
git fetch origin claude/quicksms-security-performance-dr8sw

# Merge into your working branch (keep Claude branch version for ALL conflicts)
git merge origin/claude/quicksms-security-performance-dr8sw --no-edit

# If conflicts occur: keep the INCOMING (Claude branch) version for all backend files.
# Only preserve local UI work in Blade templates where it does NOT conflict with
# status badge mappings or lifecycle logic.
```

**Conflict resolution rule:** For ALL backend files (models, services, controllers, migrations, config, routes, seeders, JS service files) → **accept incoming (Claude branch)**. Only keep your local Blade changes if they don't touch status display or lifecycle logic.

---

## Step 2: Clear All Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## Step 3: Install Dependencies

```bash
composer install --no-interaction --optimize-autoloader
```

Do NOT run `composer update`.

---

## Step 4: Run Migrations

```bash
php artisan migrate --force
```

This will run (in order):
1. `2026_03_06_000001` — Adds 4 ENUM values to `account_status`, migrates data (`trial+active` → `test_standard`, others → `active_standard`), rebuilds `account_safe_view`, adds partial indexes
2. `2026_03_06_000002` — Adds `approved_test_numbers` JSONB column to `account_settings`
3. `2026_03_06_000003` — Updates `sp_create_account` for `pending_verification` flow
4. `2026_03_06_000004` — Installs rollback safety net function (ops_admin only)

**Do NOT run `migrate:fresh`.** That destroys all data.

---

## Step 5: Verify Migration Success

```bash
# Check all migrations ran
php artisan migrate:status | grep -E "2026_03_06"
```

**Expected:** All four `2026_03_06_*` migrations show "Ran".

---

## Step 6: Verify Account Model

```bash
# Test 7-status constants exist
php artisan tinker --execute="
echo 'STATUS_PENDING_VERIFICATION: ' . App\Models\Account::STATUS_PENDING_VERIFICATION . PHP_EOL;
echo 'STATUS_TEST_STANDARD: ' . App\Models\Account::STATUS_TEST_STANDARD . PHP_EOL;
echo 'STATUS_TEST_DYNAMIC: ' . App\Models\Account::STATUS_TEST_DYNAMIC . PHP_EOL;
echo 'STATUS_ACTIVE_STANDARD: ' . App\Models\Account::STATUS_ACTIVE_STANDARD . PHP_EOL;
echo 'STATUS_ACTIVE_DYNAMIC: ' . App\Models\Account::STATUS_ACTIVE_DYNAMIC . PHP_EOL;
echo 'STATUS_SUSPENDED: ' . App\Models\Account::STATUS_SUSPENDED . PHP_EOL;
echo 'STATUS_CLOSED: ' . App\Models\Account::STATUS_CLOSED . PHP_EOL;
echo 'DEFAULT_TEST_CREDITS: ' . App\Models\Account::DEFAULT_TEST_CREDITS . PHP_EOL;
echo 'TEST_DISCLAIMER_LENGTH: ' . App\Models\Account::TEST_DISCLAIMER_LENGTH . PHP_EOL;
"
```

**Expected output:**
```
STATUS_PENDING_VERIFICATION: pending_verification
STATUS_TEST_STANDARD: test_standard
STATUS_TEST_DYNAMIC: test_dynamic
STATUS_ACTIVE_STANDARD: active_standard
STATUS_ACTIVE_DYNAMIC: active_dynamic
STATUS_SUSPENDED: suspended
STATUS_CLOSED: closed
DEFAULT_TEST_CREDITS: 100
TEST_DISCLAIMER_LENGTH: 67
```

---

## Step 7: Verify Services Instantiate

```bash
php artisan tinker --execute="
app(App\Services\TestModeEnforcementService::class); echo 'TestModeEnforcementService: OK' . PHP_EOL;
app(App\Services\FraudScreeningService::class); echo 'FraudScreeningService: OK' . PHP_EOL;
"
```

---

## Step 8: Verify Routes

```bash
php artisan route:clear
php artisan route:list 2>&1 | head -30
```

No errors = routes are clean.

---

## Step 9: Verify No Syntax Errors

```bash
php artisan config:clear
php artisan view:clear
```

If either command fails with a PHP error, there's a syntax issue. Report the exact error — do NOT try to fix application code.

---

## Step 10: Seed Database (If Fresh)

**Only run this on a fresh database or if you haven't seeded before:**

```bash
php artisan db:seed --force
```

The `SystemAccountSeeder` now uses `Account::STATUS_ACTIVE_STANDARD` constant.

---

## Step 11: Start the Server

```bash
php artisan serve --host=0.0.0.0 --port=5000
```

Or click **Run** in Replit.

---

## Step 12: Final Verification Checklist

```bash
echo "=== TEST MODE DEPLOYMENT VERIFICATION ==="

echo ""
echo "1. Migration status:"
php artisan migrate:status | grep -E "2026_03_06"

echo ""
echo "2. Account statuses in database:"
php artisan tinker --execute="
\$counts = App\Models\Account::selectRaw('account_status, COUNT(*) as cnt')
    ->groupBy('account_status')->pluck('cnt','account_status')->toArray();
print_r(\$counts);
"

echo ""
echo "3. Config loaded:"
php artisan tinker --execute="echo 'fraud_scoring url: ' . (config('services.fraud_scoring.url') ? 'SET' : 'NOT SET') . PHP_EOL;"
php artisan tinker --execute="echo 'auto_approve_threshold: ' . config('services.fraud_scoring.auto_approve_threshold') . PHP_EOL;"

echo ""
echo "4. Server test:"
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://localhost:5000

echo ""
echo "=== VERIFICATION COMPLETE ==="
```

---

## The 7-Status Lifecycle — Quick Reference

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
| `test_standard` | Test | Yes (restricted: approved numbers, registered SenderIDs, disclaimer prepended) | Test credits (100 fragments) |
| `test_dynamic` | Test | Yes (any valid E.164, any format-valid SenderID, no disclaimer) | Test credits (100 fragments) |
| `active_standard` | Live | Yes (registered SenderIDs only) | Real balance |
| `active_dynamic` | Live | Yes (any format-valid SenderID) | Real balance |
| `suspended` | — | No | No |
| `closed` | — | No | No |

### Valid Status Transitions

```
pending_verification → test_standard, test_dynamic
test_standard        → test_dynamic, active_standard, suspended, closed
test_dynamic         → test_standard, active_dynamic, suspended, closed
active_standard      → active_dynamic, suspended, closed
active_dynamic       → active_standard, suspended, closed
suspended            → test_standard, test_dynamic, active_standard, active_dynamic, closed
closed               → (nothing — terminal)
```

---

## Key Files Deployed

### New Services
| File | Purpose |
|------|---------|
| `app/Services/TestModeEnforcementService.php` | Single source of truth for test mode rules |
| `app/Services/FraudScreeningService.php` | Fraud scoring with 3-tier outcome + admin notification |

### Modified Core Files
| File | Change |
|------|--------|
| `app/Models/Account.php` | 7 status constants, grouping arrays, transition map, `transitionTo()`, `deductTestCredits()`, 12+ helpers |
| `app/Services/Campaign/CampaignService.php` | Test mode enforcement on send + scheduled dispatch |
| `app/Services/DeliveryService.php` | Per-message `validateForDelivery()` (checks without double-billing) |
| `app/Services/Campaign/PhoneNumberUtils.php` | `0044` prefix handling |
| `app/Services/SenderIdEnforcementService.php` | Dynamic accounts bypass SenderID registration |
| `app/Http/Controllers/AccountActivationController.php` | test→live preserves standard/dynamic mode |
| `app/Http/Controllers/AdminController.php` | Dashboard counts for all 7 statuses |
| `resources/views/admin/overview.blade.php` | All 7 status labels + badge colours |
| `public/js/quicksms-account-lifecycle.js` | Frontend 7-status model |
| `public/js/billing-services.js` | `AccountStatusUtil` normaliser |

### New Migrations
| Migration | Purpose |
|-----------|---------|
| `2026_03_06_000001` | ENUM values + data migration + view + indexes |
| `2026_03_06_000002` | `approved_test_numbers` JSONB column |
| `2026_03_06_000003` | `sp_create_account` → `pending_verification` |
| `2026_03_06_000004` | Rollback safety net function |

---

## Environment Variables (Set in Replit Secrets)

These are **optional** — the system works without them (fraud screening falls back to manual review):

| Variable | Purpose | Default if missing |
|----------|---------|-------------------|
| `FRAUD_SCORING_API_URL` | Fraud scoring REST API endpoint | Falls back to manual review (safe) |
| `FRAUD_SCORING_API_KEY` | API key for fraud scoring | Falls back to manual review (safe) |
| `FRAUD_SCORING_AUTO_APPROVE` | Score threshold for auto-approve | `30` |
| `FRAUD_SCORING_MANUAL_REVIEW` | Score threshold for auto-reject | `70` |

---

## AFTER DEPLOYMENT — What Replit Can Safely Work On

After merge + verification, Replit is free to build **UI/frontend only**:

| Page | What To Build | Backend Exists |
|------|--------------|----------------|
| Send Message page | Wire form UI to campaign APIs | `CampaignApiController` |
| Numbers management | Blade page calling numbers endpoints | `NumberApiController` |
| RCS content creator | Card/carousel editor | `RcsAssetController` |
| Campaign history | List with status/delivery data | `GET /api/campaigns` |
| Admin account management | Display 7 statuses with transition buttons | `Account::transitionTo()` |
| Admin fraud review | Show pending accounts, approve/reject | `FraudScreeningService::adminApprove/Reject()` |

**Build Blade + jQuery + Bootstrap 5 frontend. Do NOT create new backend routes, controllers, services, or migrations.**

### Frontend Status Display

```javascript
// Use AccountStatusUtil from billing-services.js
const display = AccountStatusUtil.normalize(account.account_status);
// Returns: { category: 'test'|'active'|'pending'|'suspended'|'closed',
//            label: 'Test (Standard)', badgeClass: 'badge-info' }
```

```php
// In Blade templates:
@if($account->isTestMode())
    <span class="badge badge-info">Test Mode</span>
@elseif($account->isLiveMode())
    <span class="badge badge-success">Live</span>
@endif
```

---

## IF SOMETHING GOES WRONG

1. **Do NOT try to fix it by modifying application code**
2. **Do NOT create workaround scripts or patches**
3. Report the exact error, which step failed, and the full error message
4. Ask before taking any corrective action

**The codebase has been through a full security review with all identified issues fixed. If deployment fails, the issue is environmental (database access, missing secrets, permissions), not code.**

---

## QUICK DEPLOY (Copy-Paste Block)

For experienced users — run this entire block after reading the prompt:

```bash
# 1. Pull
git fetch origin claude/quicksms-security-performance-dr8sw
git merge origin/claude/quicksms-security-performance-dr8sw --no-edit

# 2. Clear caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# 3. Install deps
composer install --no-interaction --optimize-autoloader

# 4. Migrate
php artisan migrate --force

# 5. Storage
php artisan storage:link --force 2>/dev/null || true
mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 6. Verify
php artisan tinker --execute="echo App\Models\Account::STATUS_TEST_STANDARD . ' ' . App\Models\Account::STATUS_ACTIVE_DYNAMIC;"

# 7. Run
php artisan serve --host=0.0.0.0 --port=5000
```

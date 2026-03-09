# Replit: Pull & Merge — Sub-Account & User Management Module (v2)

## READ THIS ENTIRE FILE BEFORE DOING ANYTHING

This prompt delivers the **Sub-Account & User Management Module** for the customer portal, along with **critical bug fixes** identified in code review. It tells you exactly which files to pull, what they do, and what to leave alone.

**Do not touch any other module. Do not refactor. Do not "improve" unrelated files.**

This module adds:
- Sub-account CRUD with limits/enforcement (spending caps, message caps, daily limits)
- User management with 7 roles, 28 permission toggles, and sender capability levels
- User invitation system with secure tokens (SHA-256 hashed, 72h expiry)
- Permission middleware for route-level access control
- Account ownership transfer
- 4 new database migrations (including security fix migration)
- **Critical auth fix**: `CustomerAuthenticate` now binds the user to Laravel's auth guard
- **Critical status fix**: `AdminController::assetsCampaigns()` uses correct status values
- **Security fixes**: `password` removed from User `$fillable`, `user_type` set in invitation acceptance, duplicate index removed

---

## ⛔ ANTI-DRIFT RULES — READ FIRST

These rules override any default Replit agent behaviour. Obey them absolutely.

1. **ONLY touch files listed in this document.** If a file is not listed below, do not open, edit, rename, move, or delete it.
2. **Do NOT modify any other module.** The following modules are FROZEN — no changes whatsoever:
   - Contact Book (`contacts/`, `ContactBookApiController`, `Contact.php`, `ContactList.php`, `OptOutList.php`, `OptOutRecord.php`, `Tag.php`)
   - Billing & Invoicing (`Billing/`, `api_billing.php`, `InvoiceApiController`, billing blade views)
   - Numbers Management (`numbers.blade.php`, `numbers-configure.blade.php`, `NumberApiController`, `NumberService`, `NumberBillingService`, `PurchasedNumber`, `VmnPoolNumber`, `ShortcodeKeyword`)
   - RCS Agent Registration (`rcs-agent-wizard.blade.php`, `rcs-agent.blade.php`, `RcsAgentController`, `RcsAgent.php`, RCS admin views)
   - API Connections (`api-connections.blade.php`, `api-connection-wizard.blade.php`, `ApiConnectionController`)
   - Spam Filter & Security (`ContentRule`, `UrlRule`, `SenderidRule`, `EnforcementExemption`)
   - Admin Login & Admin User Management (`AdminAuthController`, admin login migrations)
   - Sender ID Management (`SenderIdController`, `SenderId.php`, sender ID blade views)
   - Send Message page (`send-message.blade.php`) — do NOT edit
   - Confirm Campaign page (`confirm-campaign.blade.php`) — do NOT edit
   - Campaign History page (`campaign-history.blade.php`) — do NOT edit
   - Campaign Models & Services (`Campaign.php`, `CampaignService.php`, `DeliveryService.php`, `CampaignApiController.php`)
   - Dashboard (`dashboard.blade.php`) — do NOT edit
   - Reporting pages (`reporting/*.blade.php`) — do NOT edit
   - Purchase pages (`purchase/messages.blade.php`, `purchase/numbers.blade.php`)
   - Account Details (`account/details.blade.php`, `account/activate.blade.php`) — do NOT edit
   - Opt-Out Landing pages (`optout/*.blade.php`, `OptOutLandingController.php`)
   - Message Templates (`MessageTemplate.php`, `MessageTemplateApiController.php`, template blade views)
   - Email-to-SMS (`EmailToSmsController`, email-to-sms blade views)
   - Sidebar navigation (`elements/quicksms-sidebar.blade.php`) — do NOT edit
   - Layout (`layouts/quicksms.blade.php`, `layouts/default.blade.php`) — do NOT edit
   - All config files (`config/billing.php`, `config/app.php`, `config/services.php`)
   - `setup.sh`, `.replit`, `replit.nix`
   - All `REPLIT_PROMPT_*.md` files
   - **AppServiceProvider.php** — do NOT edit (contains test credits View::composer)
3. **Do NOT modify existing migrations.** Only the 4 new migrations listed in Section 2 are part of this build.
4. **Do NOT add packages or dependencies.**
5. **Do NOT convert PostgreSQL syntax to MySQL.** The database is PostgreSQL 16.
6. **Do NOT modify `routes/api.php` or `routes/api_billing.php`.**
7. **Do NOT modify Admin portal controllers or views** except `AdminController.php` line 449 (status fix delivered in this build).
8. **Do NOT modify the Account model (`Account.php`).**

### CRITICAL: Features That MUST Be Preserved

The following features are live on `main` and MUST NOT be deleted or modified:

| Feature | Controller Method | Route | Blade Template |
|---|---|---|---|
| Admin Account Status Change | `AdminController::updateAccountStatus()` | `PUT /admin/api/accounts/{accountId}/status` | `admin/accounts/billing.blade.php` (Account Status section + modal + JS) |
| Admin Test Numbers Management | `AdminController::saveAccountTestNumbers()` | `PUT /admin/api/accounts/{accountId}/test-numbers` | `admin/accounts/details.blade.php` (Test Numbers accordion + JS) |
| Customer Test Numbers | `QuickSMSController::saveApprovedTestNumbers()` | `PUT /account/details/test-numbers` | `quicksms/account/details.blade.php` (Test Numbers UI) |
| Test Mode in Send Message | Variables in `sendMessage()` + `confirmCampaign()` | — | `send-message.blade.php` (test modals, disclaimer, character count) |
| Test Credits Banner | `AppServiceProvider` View::composer | — | `layouts/default.blade.php` (`$test_credits_remaining_global`) |
| QuickSMS Test Sender | `getApprovedSenderIds()` test_standard logic | — | — |

**If you delete ANY of these, you have introduced a regression. Check your diffs before committing.**

---

## Step 1: Pull the Branch

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git checkout main
git merge origin/claude/quicksms-security-performance-dr8sw --no-edit
```

If there are merge conflicts:
- For files listed in Section 2 below → **keep the incoming (Claude branch) version**
- For ANY other file → **keep YOUR (main) version**

After merge:
```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
php artisan migrate --force
```

**Expected migration output — 4 migrations should run:**
```
2026_03_09_000001_add_limits_enforcement_to_sub_accounts
2026_03_09_000002_expand_user_roles_permissions
2026_03_09_000003_create_user_invitations_table
2026_03_09_000004_fix_account_owner_constraint_and_backfill
```

---

## Step 2: Files Delivered in This Build

### THE ONLY FILES YOU SHOULD CARE ABOUT:

---

### 2A. Database Migrations (4 new files — DO NOT MODIFY)

| File | What It Does |
|---|---|
| `database/migrations/2026_03_09_000001_add_limits_enforcement_to_sub_accounts.php` | Adds caps (monthly_spending_cap, monthly_message_cap, daily_send_limit), usage tracking columns, enforcement_type ENUM (warn/block/approval), sub_account_status ENUM (live/suspended/archived), hard_stop toggle to `sub_accounts` |
| `database/migrations/2026_03_09_000002_expand_user_roles_permissions.php` | Adds 3 new roles to `user_role` ENUM (messaging_manager, finance, developer), creates `sender_capability_level` ENUM, adds user-level caps, permission_toggles JSONB, is_account_owner boolean to `users` |
| `database/migrations/2026_03_09_000003_create_user_invitations_table.php` | Creates `user_invitations` table with UUID, SHA-256 hashed token, RLS policy, tenant isolation, UUID trigger |
| `database/migrations/2026_03_09_000004_fix_account_owner_constraint_and_backfill.php` | Converts `idx_users_account_owner` to UNIQUE partial index (enforces one owner per tenant), backfills `is_account_owner=true` for existing users with role='owner' |

**CRITICAL: These migrations create PostgreSQL ENUMs and RLS policies. They MUST run on PostgreSQL 16. Do NOT attempt to convert to MySQL.**

**CRITICAL: Migration 000004 backfills existing data. If you already have users with role='owner', they will automatically get `is_account_owner=true` set.**

---

### 2B. Models (3 files — 2 modified, 1 new)

| File | Status | Key Changes |
|---|---|---|
| `app/Models/SubAccount.php` | **MODIFIED** | Added status lifecycle (live/suspended/archived), enforcement engine, usage tracking with atomic SQL, limits management, `canSend()` check, `toPortalArray()` with limits/usage/enforcement_state |
| `app/Models/User.php` | **MODIFIED** | Added 7 role constants, 28-toggle permission matrix (`ROLE_DEFAULT_PERMISSIONS`), sender capability levels, `getEffectivePermissions()`, `hasPermission()`, `validateCapsAgainstSubAccount()`, `transferOwnership()`, `toPortalArray()` with permissions/limits/usage. **`password` REMOVED from `$fillable` for security** — passwords must be set explicitly, never via mass assignment |
| `app/Models/UserInvitation.php` | **NEW** | Invitation model with SHA-256 token hashing, 72h expiry, tenant global scope, `accept()` with DB transaction + RLS tenant context + explicit password hashing + `user_type='customer'`, `revoke()`, `findByToken()` |

#### SubAccount — Key Constants & Methods

| Constant/Method | Purpose |
|---|---|
| `STATUS_LIVE`, `STATUS_SUSPENDED`, `STATUS_ARCHIVED` | Sub-account status lifecycle |
| `ENFORCEMENT_WARN`, `ENFORCEMENT_BLOCK`, `ENFORCEMENT_APPROVAL` | Enforcement types when caps exceeded |
| `suspend()`, `reactivate()`, `archive()` | Status transitions (archive requires suspended first) |
| `updateLimits($limits, $updatedBy)` | Update caps with audit logging |
| `getEnforcementState()` | Returns 'normal', 'warning', 'blocked', or enforcement_type based on usage vs caps |
| `canSend()` | Checks status + enforcement rules to determine if sending is allowed |
| `recordMessageSent($parts, $cost)` | Atomic SQL counter update (race-condition safe) — TODO: wire to delivery pipeline |
| `isSpendingCapExceeded()`, `isMessageCapExceeded()`, `isDailyLimitExceeded()` | Individual cap checks |
| `isApproachingLimits()` | 80% threshold warning check |

#### User — Key Constants & Methods

| Constant/Method | Purpose |
|---|---|
| `ROLE_OWNER` through `ROLE_READONLY` (7 roles) | Role hierarchy constants |
| `ROLE_DEFAULT_PERMISSIONS` | 28-toggle boolean matrix per role (owner gets all, readonly gets view-only) |
| `SENDER_ADVANCED`, `SENDER_RESTRICTED`, `SENDER_NONE` | Sender capability levels |
| `getEffectivePermissions()` | Role defaults merged with per-user JSONB overrides |
| `hasPermission($name)` | Check single permission toggle |
| `canManageUsers()`, `canManageSubAccounts()`, `canSendMessages()` | Convenience permission checks |
| `isMainAccountUser()` | True if user has no sub_account_id (main account level) |
| `canViewSubAccount($id)` | Main admins see all; sub-account users see only their own |
| `validateCapsAgainstSubAccount()` | Throws if user caps exceed sub-account caps |
| `transferOwnership($previousOwner)` | DB transaction: demote old owner to admin, promote new owner |

#### User Roles & Permissions Matrix

| Role | Key Permissions Enabled |
|---|---|
| `owner` | ALL 28 toggles = true |
| `admin` | All except `view_billing` and `manage_security` |
| `messaging_manager` | Messaging, contacts, campaigns, reports — no admin/billing |
| `finance` | Reports, billing, export only |
| `developer` | API connections, reports, message logs only |
| `user` | Basic messaging (freeform, 1-to-1, contacts, drafts, reports) |
| `readonly` | View reports, message logs, audit logs only |

---

### 2C. Controllers (2 new files)

| File | Status | Purpose |
|---|---|---|
| `app/Http/Controllers/SubAccountController.php` | **NEW** | Sub-account CRUD, limits management, status transitions |
| `app/Http/Controllers/UserManagementController.php` | **NEW** | User CRUD, suspend/reactivate, ownership transfer, invitation CRUD, public invitation acceptance |

#### SubAccountController Endpoints

| Method | HTTP | Path | Permission |
|---|---|---|---|
| `index()` | GET | `/api/sub-accounts` | Auth only (sub-account users see only their own) |
| `store()` | POST | `/api/sub-accounts` | `permission:manage_sub_accounts` |
| `show()` | GET | `/api/sub-accounts/{id}` | Auth + `canViewSubAccount()` |
| `update()` | PUT | `/api/sub-accounts/{id}` | `permission:manage_sub_accounts` |
| `updateLimits()` | PUT | `/api/sub-accounts/{id}/limits` | Main account admin only |
| `suspend()` | PUT | `/api/sub-accounts/{id}/suspend` | `permission:manage_sub_accounts` |
| `reactivate()` | PUT | `/api/sub-accounts/{id}/reactivate` | `permission:manage_sub_accounts` |
| `archive()` | PUT | `/api/sub-accounts/{id}/archive` | `permission:manage_sub_accounts` |

#### UserManagementController Endpoints

| Method | HTTP | Path | Permission |
|---|---|---|---|
| `index()` | GET | `/api/users` | Auth only (sub-account users see only their sub-account) |
| `show()` | GET | `/api/users/{id}` | Auth + visibility check |
| `update()` | PUT | `/api/users/{id}` | `permission:manage_users` |
| `suspend()` | PUT | `/api/users/{id}/suspend` | `permission:manage_users` |
| `reactivate()` | PUT | `/api/users/{id}/reactivate` | `permission:manage_users` |
| `transferOwnership()` | POST | `/api/users/{id}/transfer-ownership` | `permission:manage_users` + must be account owner |
| `roles()` | GET | `/api/users/roles` | Auth only |
| `listInvitations()` | GET | `/api/invitations` | Auth only |
| `invite()` | POST | `/api/invitations` | `permission:manage_users` |
| `revokeInvitation()` | PUT | `/api/invitations/{id}/revoke` | `permission:manage_users` |
| `acceptInvitation()` | POST | `/invitation/accept` | **Public** (rate limited 10/min) |

---

### 2D. Middleware (1 new file)

| File | Status | Purpose |
|---|---|---|
| `app/Http/Middleware/CheckPermission.php` | **NEW** | Route-level permission check using `hasPermission()` |

Usage in routes:
```php
->middleware('permission:manage_users')           // requires this ONE permission
->middleware('permission:send_bulk,send_one_to_one')  // requires ANY of these (OR logic)
```

- Account owner (`role=owner`) bypasses all permission checks.
- Returns 401 if unauthenticated, 403 if no matching permission.

---

### 2E. Critical Bug Fixes (2 modified files)

These fixes were identified in code review and are essential for the new endpoints to work.

#### CustomerAuthenticate Middleware — Auth Guard Fix

| File | Status | Change |
|---|---|---|
| `app/Http/Middleware/CustomerAuthenticate.php` | **MODIFIED** | Added `Auth::setUser()` to bind user to Laravel's auth guard |

**Why this matters**: The new controllers (`SubAccountController`, `UserManagementController`) and the `CheckPermission` middleware all call `$request->user()`. The old middleware only used session variables — it never populated Laravel's auth guard. Without this fix, `$request->user()` returns `null` and every new endpoint fails with 401/crash.

**What changed**: After verifying the session and setting the PostgreSQL tenant context, the middleware now loads the User model and calls `Auth::setUser($user)`. This makes `$request->user()`, `auth()->user()`, and model global scopes (which check `auth()->user()->tenant_id`) all work correctly.

**The exact code:**
```php
// Bind the authenticated user to Laravel's auth guard so that
// $request->user(), auth()->user(), and model global scopes work correctly
$user = \App\Models\User::withoutGlobalScope('tenant')->find(session('customer_user_id'));
if ($user) {
    Auth::setUser($user);
}
```

**Do NOT revert this change. Do NOT remove the `Auth::setUser()` call. Without it, every sub-account and user management endpoint will fail.**

#### AdminController — Status Filter Fix

| File | Status | Change |
|---|---|---|
| `app/Http/Controllers/AdminController.php` | **MODIFIED** | Line 449: Changed `where('status', 'active')` to `whereIn('status', Account::OPERATIONAL_STATUSES)` |

**Why**: `'active'` is not a valid account status. The valid statuses are `active_standard`, `active_dynamic`, `test_standard`, `test_dynamic`, etc. The old query returned zero results.

---

### 2F. Kernel Registration

| File | Status | Change |
|---|---|---|
| `app/Http/Kernel.php` | **MODIFIED** | Added `'permission' => \App\Http\Middleware\CheckPermission::class` to `$middlewareAliases` |

---

### 2G. Routes

| File | Status | Change |
|---|---|---|
| `routes/web.php` | **MODIFIED** | Added sub-account, user management, invitation, and public acceptance routes |

**New route groups added:**

```
# Sub-Account Management API (customer.auth + throttle:60,1)
GET    /api/sub-accounts                    → SubAccountController@index
POST   /api/sub-accounts                    → SubAccountController@store        [permission:manage_sub_accounts]
GET    /api/sub-accounts/{id}               → SubAccountController@show
PUT    /api/sub-accounts/{id}               → SubAccountController@update       [permission:manage_sub_accounts]
PUT    /api/sub-accounts/{id}/limits        → SubAccountController@updateLimits [permission:manage_sub_accounts]
PUT    /api/sub-accounts/{id}/suspend       → SubAccountController@suspend      [permission:manage_sub_accounts]
PUT    /api/sub-accounts/{id}/reactivate    → SubAccountController@reactivate   [permission:manage_sub_accounts]
PUT    /api/sub-accounts/{id}/archive       → SubAccountController@archive      [permission:manage_sub_accounts]

# User Management API (customer.auth + throttle:60,1)
GET    /api/users                           → UserManagementController@index
GET    /api/users/roles                     → UserManagementController@roles
GET    /api/users/{id}                      → UserManagementController@show
PUT    /api/users/{id}                      → UserManagementController@update        [permission:manage_users]
PUT    /api/users/{id}/suspend              → UserManagementController@suspend       [permission:manage_users]
PUT    /api/users/{id}/reactivate           → UserManagementController@reactivate    [permission:manage_users]
POST   /api/users/{id}/transfer-ownership   → UserManagementController@transferOwnership [permission:manage_users]

# Invitations API (customer.auth + throttle:30,1)
GET    /api/invitations                     → UserManagementController@listInvitations
POST   /api/invitations                     → UserManagementController@invite    [permission:manage_users]
PUT    /api/invitations/{id}/revoke         → UserManagementController@revokeInvitation [permission:manage_users]

# Public (no auth — throttle:10,1)
POST   /invitation/accept                   → UserManagementController@acceptInvitation
```

---

### 2H. QuickSMSController (Modified)

| File | Status | Change |
|---|---|---|
| `app/Http/Controllers/QuickSMSController.php` | **MODIFIED** | `subAccounts()`, `subAccountDetail()`, `userDetail()` now load real data from DB with fallback to mock |

The sub-accounts page (`/account/sub-accounts`) now:
1. Tries to load real sub-accounts from DB via `SubAccount::query()` if tenant context exists
2. Falls back to existing hardcoded mock data if no tenant context or no results
3. Same pattern for `subAccountDetail()` and `userDetail()`

**CRITICAL**: This file also contains the existing `sendMessage()`, `confirmCampaign()`, `getApprovedSenderIds()`, and `saveApprovedTestNumbers()` methods with test mode logic. **Do NOT delete or modify those methods.** The only changes in this build are to `subAccounts()`, `subAccountDetail()`, and `userDetail()`.

---

## Architecture Summary

### Account Hierarchy
```
Main Account (Account)
  |-- Sub-Account A
  |     |-- User 1 (messaging_manager)
  |     +-- User 2 (user)
  |-- Sub-Account B
  |     +-- User 3 (readonly)
  +-- Main Account Users (no sub_account_id)
        |-- Owner (owner, is_account_owner=true)
        +-- Admin (admin)
```

### Four-Layer Access Control
1. **Account Scope** — Global tenant isolation via RLS + Eloquent global scopes
2. **Role** — 7 roles from owner (full access) to readonly (view-only)
3. **Sender Capability** — advanced/restricted/none (controls what types of messages user can send)
4. **Permission Toggles** — 28 granular boolean flags, role defaults + per-user JSONB overrides

### Auth Flow (Critical to Understand)
```
HTTP Request
  → CustomerAuthenticate middleware
    → Check session: customer_logged_in, customer_user_id, customer_tenant_id
    → Set PostgreSQL RLS: set_config('app.current_tenant_id', tenant_id)
    → Load User model: User::withoutGlobalScope('tenant')->find(user_id)
    → Bind to auth guard: Auth::setUser($user)
  → CheckPermission middleware (if route has ->middleware('permission:...'))
    → $request->user() returns the User model (because Auth::setUser was called)
    → Owner bypasses all checks
    → Otherwise checks user->hasPermission($permission)
  → Controller method
    → $request->user() works correctly
    → Model global scopes filter by auth()->user()->tenant_id
```

### Enforcement Flow (Sub-Account Caps)
```
Message Send Request
  → Check sub_account.canSend()
    → Is sub-account live? No → blocked
    → Is hard_stop_enabled AND any cap exceeded? → blocked
    → Is enforcement_type=block AND any cap exceeded? → blocked
    → Is enforcement_type=warn AND approaching 80%? → warning (but allowed)
    → Is enforcement_type=approval AND any cap exceeded? → requires separate approval workflow
    → Otherwise → allowed
```

### Invitation Flow
```
Admin creates invitation → POST /api/invitations
  → Record created with SHA-256(token) stored in DB
  → Raw token returned in response (for email — TODO: connect to email server)
  → Event logged

Invitee accepts → POST /invitation/accept (public, rate limited)
  → Raw token hashed and looked up
  → Invitation must be pending + not expired (72h)
  → User created in DB transaction with RLS tenant context set
  → Password set explicitly (not mass-assigned) for security
  → user_type set to 'customer'
  → Invitation marked as accepted
```

---

## Step 3: What Replit Can Do AFTER Merging

After the merge is verified (Step 5 below), Replit may **ONLY** work on these tasks:

### Task 1: Wire Sub-Account UI to Real API

The blade views at `/account/sub-accounts` and `/account/sub-accounts/{id}` currently use a mix of real data and mock data. Wire them to the new API endpoints:

```
GET  /api/sub-accounts              → List sub-accounts
GET  /api/sub-accounts/{id}         → Sub-account detail
PUT  /api/sub-accounts/{id}         → Update sub-account
PUT  /api/sub-accounts/{id}/limits  → Update limits
PUT  /api/sub-accounts/{id}/suspend → Suspend
PUT  /api/sub-accounts/{id}/reactivate → Reactivate
```

### Task 2: Wire User Management UI to Real API

The users section within sub-account detail pages. Wire to:

```
GET  /api/users?sub_account_id={id} → List users for sub-account
GET  /api/users/{id}                → User detail
PUT  /api/users/{id}                → Update user (role, permissions, caps)
PUT  /api/users/{id}/suspend        → Suspend user
PUT  /api/users/{id}/reactivate     → Reactivate user
```

### Task 3: Build Invitation UI

Create UI for managing invitations within the user management section:

```
GET  /api/invitations               → List pending invitations
POST /api/invitations               → Send new invitation
PUT  /api/invitations/{id}/revoke   → Revoke invitation
GET  /api/users/roles               → Get roles for role selector dropdown
```

### Task 4 (Optional): Build Ownership Transfer UI

Add a button/modal for the account owner to transfer ownership:

```
POST /api/users/{id}/transfer-ownership → Transfer ownership
```

### FORBIDDEN After Merge:
- Do NOT add new database migrations
- Do NOT modify the 4 migrations delivered in this build
- Do NOT modify `SubAccount.php`, `User.php`, or `UserInvitation.php` models
- Do NOT modify `SubAccountController.php` or `UserManagementController.php`
- Do NOT modify `CheckPermission.php` middleware
- Do NOT modify `CustomerAuthenticate.php` — the `Auth::setUser()` fix is essential
- Do NOT modify `Kernel.php`
- Do NOT modify the route definitions in `routes/web.php` for these endpoints
- Do NOT rename any methods, classes, or constants
- Do NOT change the PostgreSQL ENUMs or RLS policies
- Do NOT modify the permission matrix (`ROLE_DEFAULT_PERMISSIONS`)
- Do NOT convert PostgreSQL syntax to MySQL
- Do NOT modify any file not listed in Section 2
- Do NOT delete or modify `updateAccountStatus()`, `saveAccountTestNumbers()`, or `saveApprovedTestNumbers()` methods
- Do NOT delete or modify test mode logic in `sendMessage()`, `confirmCampaign()`, or `getApprovedSenderIds()`
- Do NOT delete or modify the `View::composer` in `AppServiceProvider.php`
- Do NOT delete or modify the Account Status section in `admin/accounts/billing.blade.php`
- Do NOT delete or modify the Test Numbers accordion in `admin/accounts/details.blade.php`
- Do NOT delete or modify the test numbers UI in `quicksms/account/details.blade.php`

---

## Step 4: What NOT To Touch — Explicit Freeze List

These are the modules already live on `main` or delivered in previous builds. **Changing any of these is a regression.**

| Module | Key Files | Status |
|---|---|---|
| Contact Book | `all-contacts.blade.php`, `lists.blade.php`, `opt-out-lists.blade.php`, `tags.blade.php`, `ContactBookApiController.php` | FROZEN |
| Billing & Invoicing | `Billing/*.php`, `api_billing.php`, `billing.blade.php`, `invoices.blade.php` | FROZEN |
| Numbers Management | `numbers.blade.php`, `numbers-configure.blade.php`, `NumberApiController.php`, `NumberService.php` | FROZEN |
| RCS Agent Registration | `rcs-agent-wizard.blade.php`, `rcs-agent.blade.php`, `RcsAgentController.php`, admin RCS views | FROZEN |
| API Connections | `api-connections.blade.php`, `api-connection-wizard.blade.php`, `ApiConnectionController.php` | FROZEN |
| Spam Filter | `ContentRule.php`, `UrlRule.php`, admin spam filter views | FROZEN |
| Sender ID Management | `sms-sender-id-wizard.blade.php`, `sms-sender-id.blade.php`, `SenderIdController.php` | FROZEN |
| Admin Portal | `AdminController.php` (except line 449 status fix), admin blade views, admin login/auth | FROZEN |
| Send Message | `send-message.blade.php`, `rcs-wizard.js`, `rcs-preview-renderer.js` | FROZEN |
| Confirm Campaign | `confirm-campaign.blade.php` | FROZEN |
| Campaign History | `campaign-history.blade.php`, `CampaignApiController.php` | FROZEN |
| Campaign Models | `Campaign.php`, `CampaignRecipient.php`, `CampaignService.php`, `DeliveryService.php` | FROZEN |
| Message Templates | `MessageTemplate.php`, `MessageTemplateApiController.php`, template blade views | FROZEN |
| Email-to-SMS | `EmailToSmsController.php`, email-to-sms blade views | FROZEN |
| Dashboard | `dashboard.blade.php` | FROZEN |
| Reporting | `reporting/dashboard.blade.php`, `message-log.blade.php`, `finance-data.blade.php` | FROZEN |
| Purchase Pages | `purchase/messages.blade.php`, `purchase/numbers.blade.php` | FROZEN |
| Account Pages | `account/details.blade.php`, `account/activate.blade.php`, `account/security.blade.php` | FROZEN |
| Opt-Out Landing | `optout/*.blade.php`, `OptOutLandingController.php` | FROZEN |
| Layout & Navigation | `layouts/quicksms.blade.php`, `layouts/default.blade.php`, `elements/quicksms-sidebar.blade.php`, `elements/admin-sidebar.blade.php` | FROZEN |
| AppServiceProvider | `app/Providers/AppServiceProvider.php` (contains test credits View::composer) | FROZEN |
| All Config | `config/billing.php`, `config/app.php`, `config/services.php` | FROZEN |
| Routes (other) | `routes/api.php`, `routes/api_billing.php` | FROZEN |
| Setup | `setup.sh`, `.replit`, `replit.nix` | FROZEN |
| Billing Services | `BalanceService.php`, `PricingEngine.php`, `LedgerService.php`, `InvoiceService.php` | FROZEN |
| Test Mode Features | `TestModeEnforcementService.php`, test credit wallets, test mode enforcement in `DeliveryService.php` | FROZEN |

---

## Step 5: Post-Merge Verification Checklist

Run each command. All must pass. If any fails, **stop and investigate — do not attempt to fix by editing frozen files.**

```bash
# 1. Clear all caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# 2. Run migrations
php artisan migrate --force
# Expected: 4 migrations run (2026_03_09_000001 through 000004)

# 3. Verify new API routes exist
php artisan route:list --path=api/sub-accounts | head -20
# Expected: 8 routes (index, store, show, update, limits, suspend, reactivate, archive)

php artisan route:list --path=api/users | head -20
# Expected: 7 routes (index, roles, show, update, suspend, reactivate, transfer-ownership)

php artisan route:list --path=api/invitations | head -10
# Expected: 3 routes (index, store, revoke)

php artisan route:list --path=invitation/accept | head -5
# Expected: 1 route (public acceptance)

# 4. Verify models load without errors
php artisan tinker --execute="new App\Models\SubAccount(); echo 'SubAccount OK';"
php artisan tinker --execute="new App\Models\UserInvitation(); echo 'UserInvitation OK';"
php artisan tinker --execute="echo count(App\Models\User::ROLE_DEFAULT_PERMISSIONS) . ' roles with permissions';"
# Expected: "7 roles with permissions"

# 5. Verify permission middleware is registered
php artisan tinker --execute="echo app(\Illuminate\Routing\Router::class)->getMiddleware()['permission'] ?? 'NOT FOUND';"
# Expected: App\Http\Middleware\CheckPermission

# 6. Verify Auth::setUser fix is in place
grep -n "Auth::setUser" app/Http/Middleware/CustomerAuthenticate.php
# Expected: line with Auth::setUser($user)

# 7. Verify password NOT in User $fillable
grep "'password'" app/Models/User.php
# Expected: Only in $hidden array, NOT in $fillable

# 8. Verify AdminController status fix
grep "OPERATIONAL_STATUSES" app/Http/Controllers/AdminController.php | head -3
# Expected: whereIn('status', Account::OPERATIONAL_STATUSES)

# 9. Verify NO syntax errors across the app
php artisan route:clear && php artisan config:clear

# 10. REGRESSION CHECK — verify these existing features still exist:
grep -n "updateAccountStatus" app/Http/Controllers/AdminController.php
# Expected: method definition found

grep -n "saveAccountTestNumbers" app/Http/Controllers/AdminController.php
# Expected: method definition found

grep -n "saveApprovedTestNumbers" app/Http/Controllers/QuickSMSController.php
# Expected: method definition found

grep -n "test_credits_remaining_global" app/Providers/AppServiceProvider.php
# Expected: variable found in View::composer

grep -n "test-numbers" routes/web.php
# Expected: routes for admin and customer test numbers

grep -n "updateAccountStatus\|status.*route" routes/web.php
# Expected: admin account status route found

# 11. Start server and verify pages load
php artisan serve --host=0.0.0.0 --port=5000

# Visit these URLs (after login as CUSTOMER):
# /account/sub-accounts                              → Sub-accounts list (should load real data)
# /account/sub-accounts/{id}                         → Sub-account detail

# 12. REGRESSION CHECK — verify these OTHER pages still load without errors:
# Visit: /dashboard
# Visit: /contacts/all
# Visit: /management/templates
# Visit: /management/numbers
# Visit: /management/rcs-agent
# Visit: /management/api-connections
# Visit: /messages/send                               → Must have test mode modals if test account
# Visit: /messages/campaign-history
# Visit: /purchase/numbers
# Visit: /account/details                             → Must have test numbers UI if test account
# Visit: /reporting/invoices
# Visit: /admin/dashboard (admin login)
# Visit: /admin/accounts/{id}                         → Must have test numbers accordion
# Visit: /admin/accounts/{id}/billing                 → Must have Account Status section
```

---

## Step 6: Summary of This Build

| Item | Detail |
|---|---|
| **Module** | Sub-Account & User Management (Customer Portal) |
| **Sub-Account API base** | `/api/sub-accounts` |
| **User API base** | `/api/users` |
| **Invitation API base** | `/api/invitations` |
| **Public acceptance** | `POST /invitation/accept` |
| **Roles endpoint** | `GET /api/users/roles` |
| **New models** | `UserInvitation.php` |
| **Modified models** | `SubAccount.php`, `User.php` |
| **New controllers** | `SubAccountController.php`, `UserManagementController.php` |
| **New middleware** | `CheckPermission.php` |
| **Modified middleware** | `CustomerAuthenticate.php` (Auth::setUser fix) |
| **Modified admin controller** | `AdminController.php` (status filter fix) |
| **Modified kernel** | `Kernel.php` (added `permission` alias) |
| **Modified routes** | `routes/web.php` (added 19 new routes) |
| **Modified controller** | `QuickSMSController.php` (real data fallback for sub-accounts) |
| **New migrations** | 4 (limits/enforcement, roles/permissions, invitations, owner constraint fix) |
| **PostgreSQL ENUMs created** | `sub_account_enforcement_type`, `sub_account_status`, `sender_capability_level`, `invitation_status` |
| **PostgreSQL ENUMs expanded** | `user_role` (+3 values: messaging_manager, finance, developer) |
| **RLS policies created** | `user_invitations_isolation` on `user_invitations` table |
| **Permission toggles** | 28 toggles across 7 roles |
| **Sender capability levels** | advanced, restricted, none |
| **Security features** | SHA-256 token hashing, tenant-scoped validation, UNIQUE owner constraint, atomic usage counters, rate limiting, password removed from $fillable |
| **Bug fixes** | Auth guard binding (BUG-1/BUG-2), status filter (BUG-3), password $fillable (MOD-1), user_type in invite (MOD-2), duplicate index (MOD-3) |
| **Files touched** | 16 files (4 migrations, 3 models, 2 controllers, 2 middleware, 1 kernel, 1 routes, 1 existing controller, 1 admin controller, 1 plan doc) |
| **Files frozen** | Everything else (470+ files) |
| **New dependencies** | None |
| **Risk to other modules** | Zero — backend API only, no blade view changes except QuickSMSController data source |
| **Next step for Replit** | Wire sub-account and user management blade views to the new API endpoints |

---

## ⛔ FINAL WARNING TO REPLIT AGENT

**Your job is to merge, run migrations, and verify. Then optionally wire the existing blade views to the new API endpoints. That's it.**

- If you feel the urge to "clean up" another file → **STOP**
- If you feel the urge to modify a migration → **STOP**
- If you feel the urge to refactor the User or SubAccount model → **STOP**
- If you feel the urge to modify routes beyond what was delivered → **STOP**
- If you feel the urge to improve a controller you weren't asked to touch → **STOP**
- If you feel the urge to modify the campaign module → **STOP**
- If you feel the urge to change the admin portal → **STOP**
- If you feel the urge to add a new ENUM value or modify the permission matrix → **STOP**
- If you feel the urge to change RLS policies or database triggers → **STOP**
- If a page other than sub-accounts/users has an issue → **do NOT fix it in this merge**
- If you feel the urge to delete `updateAccountStatus()` → **STOP — that's a regression**
- If you feel the urge to delete `saveAccountTestNumbers()` → **STOP — that's a regression**
- If you feel the urge to delete `saveApprovedTestNumbers()` → **STOP — that's a regression**
- If you feel the urge to remove test mode UI from `send-message.blade.php` → **STOP — that's a regression**
- If you feel the urge to remove the test credits View::composer → **STOP — that's a regression**
- If you feel the urge to remove the Account Status section from the billing page → **STOP — that's a regression**
- If you feel the urge to remove the Test Numbers accordion from admin details → **STOP — that's a regression**
- If you feel the urge to revert the `Auth::setUser()` fix in `CustomerAuthenticate.php` → **STOP — that's a critical bug fix**

**One build. Sub-accounts & users + bug fixes. Zero drift. Zero regressions.**

# QuickSMS - Replit Deployment & Integration Guide

> **Branch:** `claude/quicksms-security-performance-dr8sw`
> **Date:** 2026-02-13
> **Platform:** UK Enterprise Multi-Tenant SaaS (SMS/RCS/WhatsApp Business)
> **Compliance:** ISO27001, Cyber Essentials Plus, NHS DSP Toolkit

---

## TABLE OF CONTENTS

1. [Overview & Context](#1-overview--context)
2. [Phase 1 - PostgreSQL Database Setup](#2-phase-1---postgresql-database-setup)
3. [Phase 2 - Run Migrations & Stored Procedures](#3-phase-2---run-migrations--stored-procedures)
4. [Phase 3 - Integrate Sign-Up Page](#4-phase-3---integrate-sign-up-page)
5. [Phase 4 - Integrate Login Page](#5-phase-4---integrate-login-page)
6. [Phase 5 - Integrate Customer Portal Account Details](#6-phase-5---integrate-customer-portal-account-details)
7. [Phase 6 - Integrate Admin Console Account Details](#7-phase-6---integrate-admin-console-account-details)
8. [Security Protocols (MANDATORY)](#8-security-protocols-mandatory)
9. [Testing Plan](#9-testing-plan)
10. [File Reference Map](#10-file-reference-map)

---

## 1. Overview & Context

This branch contains a **security-hardened PostgreSQL conversion** of the QuickSMS database layer. All code has passed an Opus-level security review and all 8 identified ship-blockers have been fixed.

### What Changed (Summary)

| Area | Change |
|------|--------|
| **Database** | MySQL replaced with PostgreSQL 15+ (native UUID, ENUM, JSONB, INET types) |
| **Tenant Isolation** | Row Level Security (RLS) with `FORCE ROW LEVEL SECURITY` on all 7 tenant tables |
| **Authentication** | Stored procedures (`sp_create_account`, `sp_authenticate_user`) handle account creation and login |
| **Middleware** | `SetTenantContext` sets `app.current_tenant_id` session variable for RLS enforcement |
| **Models** | All UUID mutators removed (PostgreSQL returns native 36-char UUID strings) |
| **Password Handling** | Single-hash only in controller; double-hash bug in User::boot() removed |
| **Roles** | 4 database roles: `portal_ro`, `portal_rw`, `svc_red`, `ops_admin` |

### Architecture: RED/GREEN Trust Boundary

```
GREEN (Customer-Facing)              RED (Internal/Admin)
========================             ========================
Customer Portal UI                   Admin Console UI
  |                                    |
  v                                    v
API routes (auth:sanctum)            Admin routes (svc_red role)
  |                                    |
  v                                    v
SetTenantContext middleware           No tenant context (svc_red bypasses RLS)
  |                                    |
  v                                    v
PostgreSQL (portal_rw role)          PostgreSQL (svc_red role)
RLS enforced per tenant              RLS bypassed for admin access
```

---

## 2. Phase 1 - PostgreSQL Database Setup

### 2.1 Install PostgreSQL on Replit

In Replit Shell:
```bash
# PostgreSQL should be available. Verify:
psql --version

# If not available, add to replit.nix:
# pkgs.postgresql_15
```

### 2.2 Create the Database

```bash
# Create the database
createdb quicksms_db

# Or via psql:
psql -c "CREATE DATABASE quicksms_db;"
```

### 2.3 Run the Roles & Grants Script

**CRITICAL: This must run FIRST, before migrations, as the superuser/postgres role.**

```bash
psql -d quicksms_db -f package/database/setup/01_create_roles_and_grants.sql
```

This script creates 4 database roles:
- `portal_ro` - Read-only portal access
- `portal_rw` - Read-write portal access (used by Laravel `.env`)
- `svc_red` - Internal services (bypasses RLS)
- `ops_admin` - Operations admin (full access)

**After running, set strong passwords:**
```sql
ALTER ROLE portal_rw WITH PASSWORD 'GENERATE_STRONG_PASSWORD_HERE';
ALTER ROLE svc_red WITH PASSWORD 'GENERATE_STRONG_PASSWORD_HERE';
ALTER ROLE ops_admin WITH PASSWORD 'GENERATE_STRONG_PASSWORD_HERE';
```

### 2.4 Configure .env

Update `/package/.env` (use `.env.example` as reference):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=quicksms_db
DB_USERNAME=portal_rw
DB_PASSWORD=<the_password_you_set_above>
```

**NEVER use the postgres superuser role for the application. Always use `portal_rw`.**

---

## 3. Phase 2 - Run Migrations & Stored Procedures

### 3.1 Run All PostgreSQL Migrations

```bash
cd package
php artisan migrate --path=database/migrations
```

The migrations run in this order (controlled by filename timestamps):

**Tables (000001-000009):**
1. `2026_02_10_000001_create_accounts_table_postgres.php` - Accounts + RLS + sequence
2. `2026_02_10_000002_create_users_table_postgres.php` - Users + RLS
3. `2026_02_10_000003_create_user_sessions_table_postgres.php` - Sessions + tenant_id trigger + RLS
4. `2026_02_10_000004_create_api_tokens_table_postgres.php` - API tokens + RLS
5. `2026_02_10_000005_create_password_reset_tokens_table_postgres.php` - Password resets
6. `2026_02_10_000006_create_email_verification_tokens_table_postgres.php` - Email tokens + tenant_id trigger + RLS
7. `2026_02_10_000007_create_account_settings_table_postgres.php` - Account settings + RLS
8. `2026_02_10_000008_create_account_credits_table_postgres.php` - Credits/billing + RLS
9. `2026_02_10_000009_create_mobile_verification_attempts_table_postgres.php` - Mobile MFA

**RED Tables (100001-100004):**
10. `2026_02_10_100001_create_admin_users_table_postgres.php` - Admin users
11. `2026_02_10_100002_create_auth_audit_log_table_postgres.php` - Immutable audit log
12. `2026_02_10_100003_create_account_flags_table_postgres.php` - Account flags
13. `2026_02_10_100004_create_password_history_table_postgres.php` - Password history

**Stored Procedures (200001-200005):**
14. `2026_02_10_200001_create_sp_create_account_procedure_postgres.php` - Account creation SP
15. `2026_02_10_200002_create_sp_authenticate_user_procedure_postgres.php` - Authentication SP
16. `2026_02_10_200003_create_sp_update_user_profile_procedure_postgres.php` - Profile update SP
17. `2026_02_10_200004_create_sp_create_api_token_procedure_postgres.php` - API token SP
18. `2026_02_10_200005_create_sp_update_account_settings_procedure_postgres.php` - Settings SP

**Views (300001-300003):**
19. `2026_02_10_300001_create_account_safe_view_postgres.php` - Portal-safe account view
20. `2026_02_10_300002_create_user_profile_view_postgres.php` - Portal-safe user view
21. `2026_02_10_300003_create_api_tokens_view_postgres.php` - Portal-safe token view

### 3.2 Verify Migration Success

```bash
php artisan migrate:status
```

All migrations should show "Ran". Then verify RLS is active:

```bash
psql -d quicksms_db -c "
SELECT tablename, rowsecurity
FROM pg_tables
WHERE schemaname = 'public'
AND tablename IN ('accounts','users','user_sessions','api_tokens',
                   'email_verification_tokens','account_settings','account_credits');
"
```

**Expected:** All 7 tables should show `rowsecurity = true`.

### 3.3 Verify Stored Procedures Exist

```bash
psql -d quicksms_db -c "
SELECT routine_name, routine_type
FROM information_schema.routines
WHERE routine_schema = 'public'
AND routine_name LIKE 'sp_%';
"
```

**Expected:** 5 functions listed (sp_create_account, sp_authenticate_user, sp_update_user_profile, sp_create_api_token, sp_update_account_settings).

---

## 4. Phase 3 - Integrate Sign-Up Page

### 4.1 Current State

The sign-up page exists at:
- **Blade template:** `package/resources/views/fillow/page/register.blade.php`
- **Route:** `GET /page-register` (web route, renders the form)
- **API endpoint:** `POST /api/auth/signup` (handled by `AuthController::signup`)

The current blade template is a **static HTML form** that submits to `{{ url('index') }}` (does nothing). It needs to be wired to the API.

### 4.2 What to Implement

Modify `register.blade.php` to:

1. **Change form fields** to match the API's expected input:
   - `company_name` (required) - Business name
   - `first_name` (required)
   - `last_name` (required)
   - `job_title` (optional)
   - `email` (required, unique)
   - `phone` (optional)
   - `country` (required, 2-char code, default "GB")
   - `password` (required, 12+ chars, mixed case + numbers + symbols)
   - `password_confirmation` (required, must match)
   - `mobile_number` (required, for MFA)
   - `accept_terms` (required checkbox)
   - `accept_privacy` (required checkbox)
   - `accept_fraud_prevention` (required checkbox)
   - `accept_marketing` (optional checkbox)

2. **Submit via AJAX** to `POST /api/auth/signup`:
   ```javascript
   // Example JavaScript integration
   const form = document.getElementById('signupForm');
   form.addEventListener('submit', async function(e) {
       e.preventDefault();

       const response = await fetch('/api/auth/signup', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'Accept': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
           },
           body: JSON.stringify({
               company_name: document.getElementById('companyName').value,
               first_name: document.getElementById('firstName').value,
               last_name: document.getElementById('lastName').value,
               job_title: document.getElementById('jobTitle').value,
               email: document.getElementById('email').value,
               phone: document.getElementById('phone').value,
               country: 'GB',
               password: document.getElementById('password').value,
               password_confirmation: document.getElementById('passwordConfirmation').value,
               mobile_number: document.getElementById('mobileNumber').value,
               accept_terms: document.getElementById('acceptTerms').checked,
               accept_privacy: document.getElementById('acceptPrivacy').checked,
               accept_fraud_prevention: document.getElementById('acceptFraud').checked,
               accept_marketing: document.getElementById('acceptMarketing').checked
           })
       });

       const data = await response.json();

       if (data.status === 'success') {
           // Store Sanctum token
           localStorage.setItem('auth_token', data.data.token);
           // Redirect to email verification or dashboard
           window.location.href = '/index';
       } else {
           // Display validation errors
           displayErrors(data.errors);
       }
   });
   ```

3. **Display validation errors** inline next to each field.

4. **Show password requirements** visually (12+ chars, uppercase, lowercase, number, symbol).

### 4.3 API Response Format (from AuthController::signup)

**Success (201):**
```json
{
    "status": "success",
    "message": "Account created successfully. Please verify your email.",
    "data": {
        "user": {
            "id": "uuid-string",
            "email": "user@example.com",
            "first_name": "John",
            "last_name": "Doe",
            "account_number": "QS-00001"
        },
        "token": "sanctum-bearer-token",
        "requires_email_verification": true,
        "requires_mobile_verification": true
    }
}
```

**Validation Error (422):**
```json
{
    "status": "error",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 12 characters."]
    }
}
```

### 4.4 Security Rules for Sign-Up Integration

- **NEVER** send the raw password to any logging or analytics service
- **NEVER** store the Sanctum token in a cookie directly; use `localStorage` or `httpOnly` cookie
- **ALWAYS** include CSRF token in the request header
- The stored procedure `sp_create_account` handles all database writes atomically
- Password is hashed with `Hash::make()` in the controller BEFORE passing to the SP
- Email verification token is generated automatically by the SP

---

## 5. Phase 4 - Integrate Login Page

### 5.1 Current State

- **Blade template:** `package/resources/views/fillow/page/page_login.blade.php`
- **Route:** `GET /page-login` (web route, renders the form)
- **API endpoint:** `POST /api/auth/login` (handled by `AuthController::login`)

The current blade template has a static form with `username` and `password` fields that submits to `{{ url('index') }}`.

### 5.2 What to Implement

Modify `page_login.blade.php` to:

1. **Change fields** to match the API:
   - Change "Username" label to "Email Address" and use `type="email"`
   - Keep password field
   - Remove the hardcoded `value="123456"` from the password field

2. **Submit via AJAX** to `POST /api/auth/login`:
   ```javascript
   const form = document.getElementById('loginForm');
   form.addEventListener('submit', async function(e) {
       e.preventDefault();

       const response = await fetch('/api/auth/login', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'Accept': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
           },
           body: JSON.stringify({
               email: document.getElementById('email').value,
               password: document.getElementById('password').value
           })
       });

       const data = await response.json();

       if (data.status === 'success') {
           localStorage.setItem('auth_token', data.data.token);
           window.location.href = '/index';
       } else if (response.status === 423) {
           // Account locked
           showError(data.message);
       } else if (response.status === 401) {
           // Invalid credentials
           showError('Invalid email or password.');
       } else {
           showError(data.message || 'Login failed.');
       }
   });
   ```

3. **Wire "Forgot Password?"** link to `GET /page-forgot-password` (which should POST to `/api/auth/forgot-password`).

### 5.3 API Response Format (from AuthController::login)

**Success (200):**
```json
{
    "status": "success",
    "message": "Login successful",
    "data": {
        "user": { "id": "...", "email": "...", "first_name": "...", "last_name": "..." },
        "token": "sanctum-bearer-token",
        "account": { "company_name": "...", "account_number": "QS-00001" }
    }
}
```

**Account Locked (423):**
```json
{
    "status": "error",
    "message": "Account is temporarily locked due to too many failed attempts. Try again after [time]."
}
```

**Invalid Credentials (401):**
```json
{
    "status": "error",
    "message": "Invalid credentials"
}
```

### 5.4 How Login Works Internally

1. Controller validates email + password format
2. Finds user by email (via Eloquent, no tenant scope needed for login)
3. Verifies password with `Hash::check($request->password, $user->password)`
4. Calls `SELECT * FROM sp_authenticate_user(email, ip, password_verified_flag)` which:
   - Checks if account is locked (`locked_until` column)
   - Increments `failed_login_count` on failure (locks after 5 failures)
   - Resets `failed_login_count` on success
   - Logs the auth event in `auth_audit_log`
5. Creates Sanctum token on success
6. Sets tenant context for the session

### 5.5 Security Rules for Login Integration

- **NEVER** reveal whether the email exists or not (use generic "Invalid credentials" message)
- **NEVER** remove the hardcoded password `value="123456"` from the template — wait, **DO** remove it (it's a security risk)
- **ALWAYS** rate-limit login attempts (already handled by `ThrottleRequests::class.':api'` middleware)
- **ALWAYS** display lockout status to user when account is locked (423 response)

---

## 6. Phase 5 - Integrate Customer Portal Account Details

### 6.1 Current State

There is **no existing Customer Portal account details blade template**. The account data is accessed via the API:

- **API endpoint (GET):** `GET /api/account` -> `AccountController::show`
- **API endpoint (PUT):** `PUT /api/account` -> `AccountController::update`
- **API endpoint (PUT):** `PUT /api/account/settings` -> `AccountController::updateSettings`

### 6.2 What to Build

Create a **Customer Portal** account details page that:

1. **Loads account data** from `GET /api/account` on page load
2. **Displays account information** in sections:
   - Company Name, Account Number (read-only), Status
   - Contact details (phone, address)
   - Account settings (timezone, language, notification preferences)
3. **Allows editing** via `PUT /api/account` and `PUT /api/account/settings`
4. **Shows team members** from `GET /api/account/team`

### 6.3 API Authentication for Portal Pages

All Customer Portal API calls must include the Sanctum bearer token:

```javascript
async function apiCall(method, url, body = null) {
    const token = localStorage.getItem('auth_token');
    const headers = {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    };
    if (body) headers['Content-Type'] = 'application/json';

    const response = await fetch(url, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null
    });

    if (response.status === 401) {
        // Token expired, redirect to login
        localStorage.removeItem('auth_token');
        window.location.href = '/page-login';
        return null;
    }

    return await response.json();
}

// Usage:
const accountData = await apiCall('GET', '/api/account');
```

### 6.4 API Response Format (from AccountController::show)

```json
{
    "status": "success",
    "data": {
        "account": {
            "id": "uuid",
            "account_number": "QS-00001",
            "company_name": "Acme Corp",
            "status": "active",
            "phone": "+44...",
            "address_line1": "...",
            "city": "...",
            "postcode": "...",
            "country": "GB",
            "vat_number": "GB123456789"
        },
        "settings": {
            "timezone": "Europe/London",
            "language": "en",
            "notification_email": true,
            "notification_sms": false
        }
    }
}
```

### 6.5 Security Rules for Customer Portal

- **ALWAYS** use the Sanctum bearer token (never pass account_id or tenant_id in the URL)
- **NEVER** allow the customer to change their `account_number` or `tenant_id`
- The `SetTenantContext` middleware automatically scopes all database queries to the authenticated tenant
- Only users with `owner` or `admin` role can update account details (enforced by `AccountController::update`)
- Account data is served through the `toPortalArray()` method which **excludes RED-only fields**

---

## 7. Phase 6 - Integrate Admin Console Account Details

### 7.1 Current State

The Admin Console account details page exists at:
- **Blade template:** `resources/views/admin/accounts/details.blade.php`
- **Route:** Loaded via admin routes (requires admin auth)
- **Current data:** Hardcoded mock data in JavaScript (`accountData` object with fake entries)

### 7.2 What to Implement

Replace the **hardcoded mock data** with real database calls. The Admin Console operates in the **RED trust zone** and uses the `svc_red` database role.

#### 7.2.1 Create Admin API Endpoints

Create new admin-only API routes (e.g., in `package/routes/admin.php`):

```php
// Admin Account API (RED zone - svc_red role, no tenant scoping)
Route::prefix('api/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/accounts/{accountId}', [AdminAccountController::class, 'show']);
    Route::put('/accounts/{accountId}', [AdminAccountController::class, 'update']);
    Route::put('/accounts/{accountId}/section/{section}', [AdminAccountController::class, 'updateSection']);
});
```

#### 7.2.2 Create AdminAccountController

```php
class AdminAccountController extends Controller
{
    public function show($accountId)
    {
        // Admin queries run as svc_red role - no RLS restriction
        // But ALWAYS validate the admin user has permission
        $account = Account::with(['settings', 'users'])->findOrFail($accountId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'account' => $account,         // Full data, not toPortalArray()
                'settings' => $account->settings,
                'users' => $account->users,
            ]
        ]);
    }
}
```

#### 7.2.3 Replace Hardcoded Data in details.blade.php

In `resources/views/admin/accounts/details.blade.php`, replace the hardcoded JavaScript `accountData` object (lines 685-699) with an API call:

```javascript
document.addEventListener('DOMContentLoaded', async function() {
    var accountId = '{{ $account_id }}';

    // Fetch real data from admin API
    const response = await fetch('/api/admin/accounts/' + accountId, {
        headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + getAdminToken()
        }
    });

    if (!response.ok) {
        document.getElementById('accountName').textContent = 'Error loading account';
        return;
    }

    const result = await response.json();
    const account = result.data.account;
    const users = result.data.users;

    // Populate Sign Up Details section
    document.getElementById('accountName').textContent = account.company_name;
    document.getElementById('signupBusinessName').value = account.company_name;
    document.getElementById('signupEmail').value = users[0]?.email || '';
    document.getElementById('signupMobile').value = users[0]?.mobile_number || '';
    document.getElementById('signupFirstName').value = users[0]?.first_name || '';
    document.getElementById('signupLastName').value = users[0]?.last_name || '';
    document.getElementById('signupJobTitle').value = users[0]?.job_title || '';

    // Populate Company Information section
    document.getElementById('companyName').value = account.company_name;
    document.getElementById('tradingName').value = account.trading_name || '';
    document.getElementById('companyNumber').value = account.company_number || '';
    document.getElementById('companyWebsite').value = account.website || '';
    document.getElementById('regAddress1').value = account.address_line1 || '';
    document.getElementById('regAddress2').value = account.address_line2 || '';
    document.getElementById('regCity').value = account.city || '';
    document.getElementById('regPostcode').value = account.postcode || '';

    // Populate VAT section
    document.getElementById('vatNumber').value = account.vat_number || '';

    // Populate Support & Operations from settings
    const settings = result.data.settings;
    if (settings) {
        document.getElementById('billingEmail').value = settings.billing_email || '';
        document.getElementById('supportEmail').value = settings.support_email || '';
        document.getElementById('incidentEmail').value = settings.incident_email || '';
    }
});
```

#### 7.2.4 Wire Save Buttons to API

Replace the `confirmSaveChanges()` function (currently a no-op) with real API calls:

```javascript
async function confirmSaveChanges() {
    if (saveConfirmModal) saveConfirmModal.hide();

    var accountId = '{{ $account_id }}';
    var sectionData = {};

    // Collect data based on which section is being saved
    switch (currentSectionName) {
        case 'Sign Up Details':
            sectionData = {
                first_name: document.getElementById('signupFirstName').value,
                last_name: document.getElementById('signupLastName').value,
                job_title: document.getElementById('signupJobTitle').value,
                email: document.getElementById('signupEmail').value,
                mobile_number: document.getElementById('signupMobile').value,
                company_name: document.getElementById('signupBusinessName').value
            };
            break;
        case 'Company Information':
            sectionData = {
                company_name: document.getElementById('companyName').value,
                trading_name: document.getElementById('tradingName').value,
                company_number: document.getElementById('companyNumber').value,
                website: document.getElementById('companyWebsite').value,
                address_line1: document.getElementById('regAddress1').value,
                address_line2: document.getElementById('regAddress2').value,
                city: document.getElementById('regCity').value,
                postcode: document.getElementById('regPostcode').value,
                country: document.getElementById('regCountry').value
            };
            break;
        // ... other sections
    }

    const response = await fetch('/api/admin/accounts/' + accountId + '/section/' + encodeURIComponent(currentSectionName), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + getAdminToken(),
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(sectionData)
    });

    if (response.ok) {
        var toastEl = document.getElementById('saveSuccessToast');
        toastEl.querySelector('.toast-body').innerHTML =
            '<i class="fas fa-check-circle me-2"></i>' + currentSectionName + ' saved successfully!';
        new bootstrap.Toast(toastEl).show();
    } else {
        alert('Failed to save. Please try again.');
    }
}
```

### 7.3 Security Rules for Admin Console

- **Admin routes MUST use a separate auth guard** (not the same as customer Sanctum tokens)
- **Admin database queries should use `svc_red` role** which bypasses RLS (needed to access all accounts)
- **All admin actions MUST be logged** to `auth_audit_log` (INSERT-only for non-ops_admin)
- **NEVER expose stored procedure internals** (SECURITY DEFINER functions) to the admin JavaScript
- **Validate admin permissions server-side** — never trust client-side role checks alone
- The Admin Console should use the `AdminControlPlane.logAdminAction()` function already present in the template for frontend audit logging

---

## 8. Security Protocols (MANDATORY)

### 8.1 Rules That Must NEVER Be Violated

| Rule | Description |
|------|-------------|
| **S1** | `tenant_id` is NEVER derived from user input, URL parameters, or request headers. It comes ONLY from the authenticated user record. |
| **S2** | All database writes for account creation go through `sp_create_account()` stored procedure. Never use `Account::create()` or `User::create()` directly for signup. |
| **S3** | All authentication checks go through `sp_authenticate_user()` stored procedure. Never implement lockout/audit logic in PHP. |
| **S4** | Password is hashed ONCE in the controller with `Hash::make()`, then passed pre-hashed to the SP. The `User::boot()` method does NOT hash passwords. |
| **S5** | The `SetTenantContext` middleware MUST remain in the `api` middleware group in `Kernel.php`. Do not remove or reorder it. |
| **S6** | PostgreSQL RLS is the primary security boundary. Eloquent global scopes are defense-in-depth only. |
| **S7** | Portal pages MUST use `toPortalArray()` methods on models. Never return raw model data to GREEN-zone responses. |
| **S8** | Stored procedures use `SECURITY DEFINER` with `search_path = public, pg_temp`. Never change the search_path. |
| **S9** | Audit log (`auth_audit_log`) is INSERT-only for `portal_rw` and `svc_red` roles. Never grant UPDATE or DELETE. |
| **S10** | CSRF tokens MUST be included in all POST/PUT/DELETE requests from blade templates. |

### 8.2 Middleware Stack (Do Not Modify Order)

In `package/app/Http/Kernel.php`, the API middleware group is:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
    \App\Http\Middleware\SetTenantContext::class,  // MUST be last - runs after auth
],
```

### 8.3 Database Connection Security

- Application connects as `portal_rw` (has RLS enforced)
- Admin tools connect as `svc_red` (bypasses RLS, used for cross-tenant admin queries)
- Never connect as `postgres` or a superuser role from the application
- The `FORCE ROW LEVEL SECURITY` on all tables means even the table owner cannot bypass RLS

---

## 9. Testing Plan

### Phase T1: Database Layer Verification

| # | Test | How to Verify | Expected |
|---|------|---------------|----------|
| T1.1 | Migrations run without errors | `php artisan migrate:status` | All 21 migrations show "Ran" |
| T1.2 | RLS is enabled on tenant tables | SQL query in Section 3.2 | 7 tables with `rowsecurity = true` |
| T1.3 | Stored procedures exist | SQL query in Section 3.3 | 5 functions listed |
| T1.4 | Roles and grants applied | `\du` in psql | 4 custom roles visible |
| T1.5 | FORCE RLS active | `SELECT relname, relforcerowsecurity FROM pg_class WHERE relname IN ('accounts','users','user_sessions','api_tokens','email_verification_tokens','account_settings','account_credits');` | All show `relforcerowsecurity = true` |

### Phase T2: Sign-Up Flow

| # | Test | Steps | Expected |
|---|------|-------|----------|
| T2.1 | Successful signup | Fill all required fields, submit | 201 response, user created in DB, Sanctum token returned |
| T2.2 | Duplicate email rejection | Sign up with existing email | 422 with "email has already been taken" |
| T2.3 | Weak password rejection | Use "password123" | 422 with password validation errors |
| T2.4 | Missing required fields | Submit empty form | 422 with per-field validation errors |
| T2.5 | Account number generated | Check new account in DB | Unique `QS-XXXXX` number assigned |
| T2.6 | Tenant isolation | Sign up two accounts, verify each sees only own data | Account A cannot see Account B's data |
| T2.7 | Audit log entry | Check `auth_audit_log` after signup | SIGNUP event logged with IP |

### Phase T3: Login Flow

| # | Test | Steps | Expected |
|---|------|-------|----------|
| T3.1 | Successful login | Correct email + password | 200, token returned, user data in response |
| T3.2 | Wrong password | Correct email, wrong password | 401, "Invalid credentials" |
| T3.3 | Non-existent email | Unknown email | 401, "Invalid credentials" (same message as wrong password) |
| T3.4 | Account lockout | 5 failed attempts | 423, account locked message |
| T3.5 | Lockout timer | Wait for lockout to expire | Login succeeds after timer |
| T3.6 | Audit trail | Check `auth_audit_log` | LOGIN_SUCCESS or LOGIN_FAILED events |

### Phase T4: Tenant Isolation (CRITICAL)

| # | Test | Steps | Expected |
|---|------|-------|----------|
| T4.1 | RLS blocks cross-tenant read | Login as User A, query User B's account via API | Empty result or 404 |
| T4.2 | RLS blocks cross-tenant write | Login as User A, try PUT /api/account with User B's data | Rejected |
| T4.3 | No tenant context = no data | Remove SetTenantContext middleware temporarily, query accounts | Empty results (fail-closed) |
| T4.4 | Middleware sets context correctly | Login, check `SELECT current_setting('app.current_tenant_id')` in middleware debug log | Matches user's tenant_id |

### Phase T5: Customer Portal Account Details

| # | Test | Steps | Expected |
|---|------|-------|----------|
| T5.1 | Load account data | Login, visit account page | Account details displayed correctly |
| T5.2 | Update company name | Change company name, save | 200, database updated |
| T5.3 | Non-admin blocked | Login as regular user, try PUT /api/account | 403, "Only account owners and admins" |
| T5.4 | Settings update | Update timezone, save | Settings saved via sp_update_account_settings |

### Phase T6: Admin Console Account Details

| # | Test | Steps | Expected |
|---|------|-------|----------|
| T6.1 | Load account data | Visit admin account details page | Real data loaded from DB (not mock data) |
| T6.2 | Save section changes | Edit Company Information, save | Changes persisted, audit logged |
| T6.3 | View all accounts | Admin can see accounts from all tenants | RLS bypassed for svc_red role |
| T6.4 | Audit trail | Save changes, check auth_audit_log | ACCOUNT_DETAILS_UPDATED event |

### Phase T7: Security Regression

| # | Test | Steps | Expected |
|---|------|-------|----------|
| T7.1 | No UUID corruption | Create account, read user.id | 36-character UUID string (not 72-char hex) |
| T7.2 | No double-hashing | Sign up, then login with same password | Login succeeds (proves single hash) |
| T7.3 | SP column names correct | Trigger lockout, check DB | `locked_until` column populated (not `account_locked_until`) |
| T7.4 | CSRF required | POST to /api/auth/signup without CSRF token | 419 (CSRF mismatch) |
| T7.5 | Rate limiting works | Send 60+ requests in 1 minute | 429 Too Many Requests |

---

## 10. File Reference Map

### Models (package/app/Models/)
| File | Purpose |
|------|---------|
| `Account.php` | Account model - UUID primary key, `toPortalArray()` method |
| `User.php` | User model - tenant scoped global scope, no password auto-hash |
| `ApiToken.php` | API token model - tenant scoped |
| `AccountSettings.php` | Per-account settings |
| `AccountCredit.php` | Account credits/billing |
| `AuthAuditLog.php` | Immutable audit log |
| `EmailVerificationToken.php` | Email verification tokens |
| `AdminUser.php` | RED-zone admin users |
| `PasswordHistory.php` | Password history tracking |
| `AccountFlags.php` | Account flags (locked, suspended, etc.) |

### Controllers (package/app/Http/Controllers/)
| File | Purpose |
|------|---------|
| `Auth/AuthController.php` | Signup, login, logout, email verification, password reset |
| `Auth/MobileVerificationController.php` | Mobile number verification for MFA |
| `AccountController.php` | Account details, settings, team management |
| `AccountActivationController.php` | 5-section account activation flow |

### Middleware (package/app/Http/Middleware/)
| File | Purpose |
|------|---------|
| `SetTenantContext.php` | Sets `app.current_tenant_id` PostgreSQL session variable |

### Routes (package/routes/)
| File | Purpose |
|------|---------|
| `auth.php` | All authentication and account management API routes |
| `web.php` | Web routes including login/register page rendering |
| `api.php` | Base API routes |

### Migrations (package/database/migrations/)
| Pattern | Count | Purpose |
|---------|-------|---------|
| `*_000*_*_postgres.php` | 9 | GREEN zone tables |
| `*_100*_*_postgres.php` | 4 | RED zone tables |
| `*_200*_*_postgres.php` | 5 | Stored procedures |
| `*_300*_*_postgres.php` | 3 | Database views |

### Database Setup
| File | Purpose |
|------|---------|
| `package/database/setup/01_create_roles_and_grants.sql` | Creates roles, grants, schema lockdown |

### Blade Templates
| File | Purpose |
|------|---------|
| `package/resources/views/fillow/page/page_login.blade.php` | Login page (needs API wiring) |
| `package/resources/views/fillow/page/register.blade.php` | Sign-up page (needs API wiring) |
| `resources/views/admin/accounts/details.blade.php` | Admin account details (needs real data) |
| `resources/views/admin/accounts/overview.blade.php` | Admin accounts list |
| `resources/views/admin/accounts/billing.blade.php` | Admin billing page |

### Configuration
| File | Purpose |
|------|---------|
| `package/.env.example` | Environment template (pgsql, portal_rw) |
| `package/app/Http/Kernel.php` | Middleware registration |

---

## Quick Start Summary

```bash
# 1. Pull the branch
git checkout claude/quicksms-security-performance-dr8sw
git pull origin claude/quicksms-security-performance-dr8sw

# 2. Set up PostgreSQL
createdb quicksms_db
psql -d quicksms_db -f package/database/setup/01_create_roles_and_grants.sql
# Set passwords for roles (see Phase 1)

# 3. Configure .env
cp package/.env.example package/.env
# Edit .env with your DB password and APP_KEY

# 4. Install dependencies
cd package && composer install

# 5. Generate app key
php artisan key:generate

# 6. Run migrations
php artisan migrate

# 7. Verify
php artisan migrate:status
# All 21 migrations should show "Ran"

# 8. Start server
php artisan serve
```

Then proceed with UI integration (Phases 3-6 above).

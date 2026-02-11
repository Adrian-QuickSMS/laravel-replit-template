# QuickSMS Database Deployment Instructions for Replit AI

## CONTEXT
You are deploying the QuickSMS Laravel application that has been built with a secure multi-tenant database architecture. The database code has been committed to the Git repository on branch `claude/review-codebase-1VxG3` and is ready for deployment.

## YOUR MISSION
1. Pull the latest database schema from Git
2. Configure the MariaDB/MySQL database connection
3. Run all migrations to create the complete database structure
4. Connect the backend API to the frontend UI
5. Verify the deployment is working correctly

---

## STEP 1: PULL LATEST CODE FROM GIT

### Commands to Execute:
```bash
cd /home/user/laravel-replit-template
git fetch origin claude/review-codebase-1VxG3
git checkout claude/review-codebase-1VxG3
git pull origin claude/review-codebase-1VxG3
```

### What Was Updated:
The following database components were built and committed:
- **33 migration files** creating tables, views, stored procedures, and triggers
- **20+ model files** with Eloquent ORM relationships
- **Multi-tenant architecture** with RED/GREEN data separation
- **Account activation module** (5-section progressive signup)
- **Authentication & authorization** (signup, login, MFA, API tokens)
- **Routing rules module** for SMS gateway management
- **Supplier rate cards module** for pricing

---

## STEP 2: CONFIGURE DATABASE CONNECTION

### Update `.env` File:
```bash
# Check if .env exists, if not copy from .env.example
cp .env.example .env

# Update database credentials in .env:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quicksms_db
DB_USERNAME=root
DB_PASSWORD=<your-database-password>
```

### Replit-Specific Configuration:
If using Replit's built-in database, the connection details may be different:
```bash
# For Replit MySQL:
DB_HOST=${REPLIT_DB_HOST}
DB_PORT=${REPLIT_DB_PORT}
DB_DATABASE=${REPLIT_DB_NAME}
DB_USERNAME=${REPLIT_DB_USER}
DB_PASSWORD=${REPLIT_DB_PASSWORD}
```

### Verify Database Connection:
```bash
cd package
php artisan db:show
```

Expected output: Database connection details and table count.

---

## STEP 3: RUN DATABASE MIGRATIONS

### Execute All Migrations:
```bash
cd /home/user/laravel-replit-template/package
php artisan migrate:fresh --seed
```

This will create:
- **GREEN SIDE TABLES** (Customer-accessible):
  - `accounts` - Tenant root table (UUID BINARY(16) primary key)
  - `users` - Customer portal users (tenant-scoped)
  - `api_tokens` - API authentication tokens
  - `account_settings` - Per-account configuration
  - `account_credits` - Promotional and purchased credits
  - `user_sessions` - Active login sessions
  - `mobile_verification_attempts` - SMS OTP tracking
  - `password_reset_tokens` - Password recovery
  - `email_verification_tokens` - Email verification
  - `password_history` - Previous passwords (breach prevention)

- **RED SIDE TABLES** (Internal-only):
  - `account_flags` - Fraud risk, payment status, limits (NEVER exposed to customers)
  - `auth_audit_log` - Immutable authentication event log
  - `admin_users` - Internal staff accounts (separate from customer users)
  - `rate_card_audit_log` - Pricing change history
  - `routing_audit_log` - Routing configuration changes

- **PLATFORM TABLES** (Operations):
  - `suppliers` - SMS gateway providers
  - `gateways` - Individual gateway connections
  - `mcc_mnc_master` - Mobile network identification
  - `rate_cards` - Per-network pricing
  - `fx_rates` - Currency conversion rates
  - `routing_rules` - SMS routing logic
  - `routing_gateway_weights` - Load balancing configuration
  - `routing_customer_overrides` - Per-account custom routing

- **VIEWS** (Portal-safe projections):
  - `account_safe_view` - Account data without internal flags
  - `user_profile_view` - User data without password hashes
  - `api_tokens_view` - API tokens without hashes (prefix only)

- **STORED PROCEDURES** (Business logic):
  - `sp_create_account` - Signup flow (creates account + owner user + defaults)
  - `sp_authenticate_user` - Login validation with audit logging
  - `sp_update_user_profile` - Safe user updates
  - `sp_create_api_token` - API token creation with validation
  - `sp_update_account_settings` - Account settings management

### Verify Migration Success:
```bash
php artisan migrate:status
```

All migrations should show "Ran" status.

---

## STEP 4: UNDERSTAND THE ARCHITECTURE

### Multi-Tenant Isolation Model:
**Critical Security Principle:** ZERO cross-tenant data leakage.

```
TENANT ISOLATION STRATEGY:
┌─────────────────────────────────────────────────────────┐
│ accounts table (tenant root)                            │
│ - id: UUID BINARY(16) - prevents enumeration            │
│ - account_number: QS00000001 (human-readable)           │
└─────────────────────────────────────────────────────────┘
                          │
                          │ tenant_id FK (CASCADE DELETE)
                          │
        ┌─────────────────┼─────────────────┐
        ▼                 ▼                 ▼
    ┌───────┐      ┌──────────┐      ┌────────────┐
    │ users │      │ api_tokens│      │ account_   │
    │       │      │          │      │ settings   │
    └───────┘      └──────────┘      └────────────┘

ALL GREEN tables have tenant_id with:
- Composite indexes: (tenant_id, other_field)
- Composite unique constraints: (tenant_id, field)
- Foreign key cascade: tenant_id → accounts.id ON DELETE CASCADE
- Database triggers: MUST NOT be NULL
```

### RED/GREEN Data Separation:

**GREEN SIDE** (Customer Portal Accessible):
- Data customers can see/edit about themselves
- Accessed via VIEWS only (never direct table access)
- Portal database user: `portal_ro` (read), `portal_rw` (stored procedures only)
- Examples: accounts, users, api_tokens, settings

**RED SIDE** (Internal Operations Only):
- Risk scores, fraud flags, payment status, abuse limits
- Internal staff notes, investigations
- Raw audit logs that could reveal enumeration attacks
- Portal database user: NO ACCESS
- Internal services: `svc_red` role
- Examples: account_flags, auth_audit_log, admin_users

**Data Flow:**
```
┌──────────────┐         ┌──────────────┐         ┌──────────────┐
│  Customer    │  HTTPS  │   Laravel    │   SQL   │   MariaDB    │
│  Portal UI   │────────▶│   API        │────────▶│   Database   │
│              │         │  (portal_rw) │         │              │
└──────────────┘         └──────────────┘         └──────────────┘
                                │                          │
                                │ Reads: Views only        │
                                │ Writes: Stored procs     │
                                │ Never: Base tables       │
                                │                          │
┌──────────────┐         ┌──────────────┐                │
│  Internal    │  HTTPS  │   Laravel    │                │
│  Ops Tools   │────────▶│   Services   │────────────────┘
│              │         │  (svc_red)   │  Direct table access
└──────────────┘         └──────────────┘  + Views + Procs
```

### Why This Architecture:

1. **Prevents SQL Injection Escalation**: Even if SQL injection occurs in portal, attacker cannot:
   - Read account_flags (fraud risk, payment status)
   - Read auth_audit_log (reveals other users' login attempts)
   - Modify base tables (writes go through stored procedures with validation)

2. **Enforces Business Rules**: Stored procedures validate:
   - Tenant membership (user really belongs to tenant_id)
   - Authorization (user has permission to perform action)
   - Data integrity (unique constraints, foreign keys)
   - Audit logging (every write creates audit event)

3. **Audit Trail**: All actions are logged:
   - Who (user ID + email)
   - What (event type: login, api_token_created, etc.)
   - When (timestamp)
   - Where (IP address, user agent)
   - Result (success, failure, suspicious)

---

## STEP 5: CONNECT BACKEND TO FRONTEND UI

### Frontend Pages → Backend Endpoints → Database Objects

The UI has been designed with the following screens. Here's how they connect to the database:

### 1. AUTHENTICATION MODULE

#### **Sign Up Page (Steps 1-3)**

**UI Flow:**
1. Step 1/3: Company & Personal Details
2. Step 2/3: Password & Mobile Number
3. Step 3/3: Security & Consent (Fraud Prevention + Marketing opt-in)

**Backend Endpoint:**
```
POST /api/auth/signup
```

**Request Body:**
```json
{
  "company_name": "Acme Ltd",
  "first_name": "John",
  "last_name": "Doe",
  "job_title": "CTO",
  "email": "john@acme.com",
  "phone": "+44123456789",
  "country": "GB",
  "password": "SecurePass123!@#",
  "password_confirmation": "SecurePass123!@#",
  "mobile_number": "07912345678",
  "accept_terms": true,
  "accept_privacy": true,
  "accept_fraud_prevention": true,
  "accept_marketing": false,
  "utm_source": "google",
  "utm_medium": "cpc",
  "utm_campaign": "uk-launch",
  "referrer": "https://google.com"
}
```

**Laravel Controller:**
```
package/app/Http/Controllers/Auth/AuthController.php
- Method: signup()
```

**Database Access:**
1. Validates all fields
2. Hashes password (Argon2id)
3. Normalizes mobile number (447XXXXXXXXX format)
4. Calls stored procedure:
   ```sql
   CALL sp_create_account(
     'Acme Ltd',
     'john@acme.com',
     '$2y$12$hashedpassword...',
     'John',
     'Doe',
     '+44123456789',
     'GB',
     '192.168.1.1'
   )
   ```
5. Procedure creates:
   - `accounts` row (tenant root)
   - `users` row (owner)
   - `account_settings` row (defaults)
   - `account_flags` row (RED - fraud score 0, limits)
   - `auth_audit_log` entry (signup_completed event)
6. Returns account_id + user_id + account_number
7. Sends mobile verification code

**Mobile Verification:**
```
POST /api/auth/verify-mobile
Body: { "code": "123456" }
```

Controller: `AuthController::verifyMobile()`
- Checks `mobile_verification_code` hash in users table
- Updates `mobile_verified_at`
- Awards 100 promotional credits if marketing consent given
- Inserts into `account_credits` table

---

#### **Login Page**

**Backend Endpoint:**
```
POST /api/auth/login
Body: { "email": "john@acme.com", "password": "SecurePass123!@#" }
```

**Laravel Controller:**
```
package/app/Http/Controllers/Auth/AuthController.php
- Method: login()
```

**Database Access:**
1. Calls stored procedure:
   ```sql
   CALL sp_authenticate_user(
     'john@acme.com',
     '$2y$12$hashedpassword...',
     '192.168.1.1',
     'Mozilla/5.0...'
   )
   ```
2. Procedure validates:
   - User exists
   - Password matches (bcrypt verify)
   - Account not suspended/closed
   - User not locked
   - MFA requirement
3. On success:
   - Updates `last_login_at`, `last_login_ip` in users table
   - Resets `failed_login_attempts` to 0
   - Creates session in `user_sessions` table
   - Logs to `auth_audit_log` (login_success)
4. On failure:
   - Increments `failed_login_attempts`
   - Logs to `auth_audit_log` (login_failed)
   - If 5 failures: locks account for 30 minutes

**Response:**
```json
{
  "access_token": "...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "user": {
    "id": "uuid",
    "email": "john@acme.com",
    "first_name": "John",
    "last_name": "Doe",
    "role": "owner",
    "tenant_id": "uuid"
  }
}
```

---

### 2. ACCOUNT ACTIVATION MODULE (5 Sections)

After signup, users complete 5 sections to activate from trial → live account:

#### **Section 1: Sign Up Details** (Auto-complete on signup)
- Company Name, Country, Email
- Terms, Privacy, Fraud Prevention consent
- Status stored in `accounts.signup_details_complete`

#### **Section 2: Company Information**

**UI Fields:**
- Company Type: UK Limited | Sole Trader | Government & NHS | Other
- Business Sector: (dropdown from config)
- Company Number: (if UK Limited - 8 digits)
- Website: (must start with https://)
- Registered Address: Line 1, Line 2, City, County, Postcode, Country
- Operating Address: (checkbox: same as registered, or separate fields)

**Backend Endpoint:**
```
POST /api/account/activation/company-info
```

**Laravel Controller:**
```
package/app/Http/Controllers/AccountActivationController.php
- Method: updateCompanyInfo()
```

**Database Access:**
- Updates `accounts` table fields:
  ```
  company_type, business_sector, website, company_number,
  address_line1, address_line2, city, county, postcode, country,
  operating_address_same_as_registered, operating_address_line1,
  operating_address_line2, operating_city, operating_county,
  operating_postcode, operating_country
  ```
- Validates: company_number required if company_type = 'uk_limited'
- Calls `updateActivationStatus()` to refresh `company_info_complete` flag

---

#### **Section 3: Support & Operations**

**UI Fields:**
- Accounts & Billing Email (mandatory)
- Support Contact Email (mandatory)
- Incident Email (mandatory)
- Support Contact: Name, Phone
- Operations Contact: Name, Email, Phone

**Backend Endpoint:**
```
POST /api/account/activation/support-operations
```

**Controller:** `AccountActivationController::updateSupportOperations()`

**Database Fields:**
```
accounts_billing_email, support_contact_email, incident_email,
support_contact_name, support_contact_phone,
operations_contact_name, operations_contact_email, operations_contact_phone
```

---

#### **Section 4: Contract Signatory**

**UI Fields:**
- Signatory Name (person authorized to sign contracts)
- Signatory Title (job title)
- Signatory Email
- Checkbox: "I confirm I am authorized to sign contracts on behalf of this company"
- IP address captured automatically

**Backend Endpoint:**
```
POST /api/account/activation/contract-signatory
```

**Controller:** `AccountActivationController::updateContractSignatory()`

**Database Fields:**
```
signatory_name, signatory_title, signatory_email,
contract_agreed (boolean), contract_signed_at (timestamp),
contract_signed_ip, contract_version
```

---

#### **Section 5: Billing, VAT & Tax**

**UI Fields:**
- Billing Email (mandatory)
- Billing Contact: Name, Phone (optional)
- Billing Address: (checkbox: same as registered, or separate)
- VAT Registered: Yes/No
  - If Yes: VAT Number (mandatory), VAT Reverse Charges (checkbox)
- Tax ID/Company Tax Reference
- Payment Terms: Immediate | Net 7 | Net 14 | Net 30 | Net 60
- Purchase Order Required: Yes/No
  - If Yes: Purchase Order Number

**Backend Endpoint:**
```
POST /api/account/activation/billing-vat
```

**Controller:** `AccountActivationController::updateBillingVat()`

**Database Fields:**
```
billing_email, billing_contact_name, billing_contact_phone,
billing_address_same_as_registered, billing_address_line1,
billing_address_line2, billing_city, billing_county,
billing_postcode, billing_country,
vat_registered, vat_number, vat_reverse_charges,
tax_id, tax_country, purchase_order_required, purchase_order_number,
payment_terms
```

**Activation Complete:**
When all 5 sections are complete:
- `activation_complete` flag set to `true`
- `activated_at` timestamp set to NOW()
- Account transitions from trial → can request live status
- Trial credits expire (handled by `AccountObserver`)

---

### 3. DASHBOARD / ACCOUNT DETAILS

**Backend Endpoint:**
```
GET /api/account/details
```

**Controller:** Returns `Account::toPortalArray()`

**Database Access:**
- Reads from `account_safe_view` (NOT direct accounts table)
- Includes activation progress: `getActivationProgress()`
- Returns JSON with all 5 sections status

**Response:**
```json
{
  "id": "uuid",
  "account_number": "QS00000123",
  "company_name": "Acme Ltd",
  "status": "active",
  "account_type": "trial",
  "activation": {
    "sections": [
      { "id": "signup_details", "name": "Sign Up Details", "complete": true },
      { "id": "company_info", "name": "Company Information", "complete": true },
      { "id": "support_operations", "name": "Support & Operations", "complete": false },
      { "id": "contract_signatory", "name": "Contract Signatory", "complete": false },
      { "id": "billing_vat", "name": "Billing, VAT and Tax", "complete": false }
    ],
    "overall_complete": false,
    "can_go_live": false
  }
}
```

---

### 4. API KEYS MANAGEMENT

**UI Features:**
- List all API keys (table with name, prefix, created date, last used, status)
- Create new API key (modal with name, permissions, IP whitelist, expiry)
- Revoke API key (confirmation modal)
- View API key details (permissions, usage stats)

**Backend Endpoints:**
```
GET /api/account/api-keys          # List all keys
POST /api/account/api-keys         # Create new key
DELETE /api/account/api-keys/{id}  # Revoke key
```

**Database Access:**
1. **List Keys:**
   ```sql
   SELECT * FROM api_tokens_view
   WHERE tenant_id = ?
   ORDER BY created_at DESC
   ```
   - View NEVER exposes `token_hash`
   - Shows `token_prefix` (first 8 chars) for identification
   - Shows `has_ip_whitelist` boolean, `ip_count` integer (not actual IPs)

2. **Create Key:**
   - Laravel generates secure random token (64 chars)
   - Displays token ONCE in UI (copy to clipboard)
   - Stores SHA-256 hash via stored procedure:
     ```sql
     CALL sp_create_api_token(
       user_id, tenant_id, name, hash, prefix,
       scopes, access_level, ip_whitelist, expires_at
     )
     ```
   - Procedure validates:
     - User belongs to tenant
     - Token name unique per tenant
     - Logs to `auth_audit_log` (api_token_created)

3. **Revoke Key:**
   - Sets `revoked_at`, `revoked_by`, `revocation_reason`
   - Logs to `auth_audit_log` (api_token_revoked)

---

### 5. USER MANAGEMENT (SUB-ACCOUNTS)

**UI Features:**
- List all users in account (table with name, email, role, status, last login)
- Invite new user (modal with email, first name, last name, role)
- Edit user role (dropdown: Owner | Admin | User | Read-only)
- Suspend/unsuspend user
- Reset user password (send email)

**Backend Endpoints:**
```
GET /api/account/users             # List users
POST /api/account/users            # Invite user
PATCH /api/account/users/{id}      # Update role/status
DELETE /api/account/users/{id}     # Delete user
```

**Database Access:**
1. **List Users:**
   ```sql
   SELECT * FROM user_profile_view
   WHERE tenant_id = ?
   ```
   - View NEVER exposes password hash
   - Shows MFA status, last login, role

2. **Invite User:**
   - Creates user with status='pending_verification'
   - Sends email verification link
   - User must set password before first login

3. **Update Role:**
   - Validates: At least one owner must remain
   - Updates `role` field in users table
   - Logs to `auth_audit_log`

---

### 6. ROUTING RULES MANAGEMENT (Internal/Admin Only)

**Backend Endpoints:**
```
GET /api/routing/rules                      # List all rules
POST /api/routing/rules                     # Create rule
PATCH /api/routing/rules/{id}               # Update rule
DELETE /api/routing/rules/{id}              # Delete rule
GET /api/routing/rules/{id}/gateway-weights # Get weights
POST /api/routing/rules/{id}/gateway-weights # Update weights
```

**Database Tables:**
- `routing_rules` - Primary routing rules (UK networks, international countries)
- `routing_gateway_weights` - Load balancing between multiple gateways
- `routing_customer_overrides` - Per-account custom routing
- `routing_audit_log` - All routing changes logged

**Key Fields:**
```
routing_rules:
  - product_type: SMS | RCS_BASIC | RCS_SINGLE
  - destination_type: UK_NETWORK | INTERNATIONAL
  - destination_code: Network prefix (e.g., "234_15" for Vodafone) or country ISO
  - destination_name: "Vodafone" or "United States"
  - primary_gateway_id: FK to gateways table
  - status: active | blocked

routing_gateway_weights:
  - routing_rule_id: FK to routing_rules
  - gateway_id: FK to gateways
  - weight: 1-100 (percentage)
```

---

### 7. SUPPLIER RATE CARDS (Internal/Admin Only)

**Backend Endpoints:**
```
GET /api/suppliers                  # List suppliers
POST /api/suppliers                 # Create supplier
GET /api/suppliers/{id}/rate-cards  # Get rate cards
POST /api/suppliers/{id}/rate-cards # Upload rate card CSV
```

**Database Tables:**
- `suppliers` - Gateway provider details
- `gateways` - Individual gateway connections
- `mcc_mnc_master` - Mobile network master data (MCC/MNC lookup)
- `rate_cards` - Per-network pricing
- `fx_rates` - Currency conversion
- `rate_card_audit_log` - Pricing changes audit trail

---

## STEP 6: TESTING THE DEPLOYMENT

### 1. Database Connection Test:
```bash
cd package
php artisan tinker
```

```php
// Test database connection
DB::connection()->getPdo();

// Count tables
DB::select("SHOW TABLES");

// Test tenant creation
$account = App\Models\Account::create([
    'company_name' => 'Test Company',
    'email' => 'test@example.com',
    'country' => 'GB'
]);

echo $account->id; // Should show UUID
echo $account->account_number; // Should show QS00000001
```

### 2. Test Stored Procedure:
```php
// Create test account via stored procedure
$result = DB::select("
    CALL sp_create_account(
        'Test Company',
        'test@example.com',
        '$2y$12$hashedpassword',
        'John',
        'Doe',
        '+44123456789',
        'GB',
        '127.0.0.1'
    )
")[0];

var_dump($result);
```

### 3. Test Views:
```php
// Query safe view
$accounts = DB::select("SELECT * FROM account_safe_view LIMIT 5");
var_dump($accounts);

// Verify password hash is NOT exposed
$users = DB::select("SELECT * FROM user_profile_view LIMIT 5");
var_dump($users); // Should NOT contain password field
```

### 4. Test API Endpoint:
```bash
# Signup endpoint
curl -X POST http://localhost:8000/api/auth/signup \
  -H "Content-Type: application/json" \
  -d '{
    "company_name": "Test Company",
    "email": "test@example.com",
    "password": "SecurePass123!@#",
    "password_confirmation": "SecurePass123!@#",
    "first_name": "John",
    "last_name": "Doe",
    "country": "GB",
    "mobile_number": "07912345678",
    "accept_terms": true,
    "accept_privacy": true,
    "accept_fraud_prevention": true
  }'
```

Expected response:
```json
{
  "status": "success",
  "data": {
    "account_id": "uuid",
    "user_id": "uuid",
    "account_number": "QS00000001"
  }
}
```

---

## STEP 7: FRONTEND INTEGRATION CHECKLIST

### Environment Variables Frontend Needs:
```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_APP_NAME=QuickSMS
```

### API Client Configuration (Frontend):
```javascript
// src/lib/api.js
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // For CSRF token
});

// Add auth token to all requests
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});
```

### UI Page Connections:

| Frontend Page | API Endpoint | HTTP Method | Database Objects Used |
|---------------|--------------|-------------|----------------------|
| `/signup` | `/api/auth/signup` | POST | `sp_create_account` procedure |
| `/login` | `/api/auth/login` | POST | `sp_authenticate_user` procedure |
| `/verify-mobile` | `/api/auth/verify-mobile` | POST | `users.mobile_verification_code` |
| `/activation/company-info` | `/api/account/activation/company-info` | POST | `accounts` table, `updateCompanyInfo()` |
| `/activation/support-operations` | `/api/account/activation/support-operations` | POST | `accounts` table |
| `/activation/contract-signatory` | `/api/account/activation/contract-signatory` | POST | `accounts` table |
| `/activation/billing-vat` | `/api/account/activation/billing-vat` | POST | `accounts` table |
| `/dashboard` | `/api/account/details` | GET | `account_safe_view` |
| `/api-keys` | `/api/account/api-keys` | GET | `api_tokens_view` |
| `/api-keys/create` | `/api/account/api-keys` | POST | `sp_create_api_token` procedure |
| `/users` | `/api/account/users` | GET | `user_profile_view` |
| `/account/settings` | `/api/account/settings` | GET/PATCH | `account_settings` table |

---

## STEP 8: COMMON ISSUES & TROUBLESHOOTING

### Issue 1: Migration Fails with "tenant_id cannot be NULL"
**Cause:** Database triggers enforcing tenant_id validation.
**Solution:** Ensure all inserts include tenant_id. Use stored procedures for multi-table inserts.

### Issue 2: Cross-Tenant Data Leakage in Queries
**Cause:** Eloquent queries not filtering by tenant_id.
**Solution:** Use global scopes:
```php
// Add to models that need tenant scoping
protected static function booted() {
    static::addGlobalScope('tenant', function ($query) {
        if (auth()->check() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
    });
}
```

### Issue 3: Portal User Cannot Read Accounts Table
**Cause:** Portal users should NOT have direct table access.
**Solution:** Always query via views:
```php
// WRONG:
$account = Account::find($id);

// CORRECT:
$account = DB::table('account_safe_view')->where('id', $id)->first();

// OR use Eloquent with a view model
```

### Issue 4: API Token Not Working
**Cause:** Token hash mismatch or expired token.
**Debug:**
```php
$token = 'qs_live_abcdef1234567890...';
$hash = hash('sha256', $token);
$record = DB::table('api_tokens')
    ->where('token_hash', $hash)
    ->first();

if (!$record) {
    // Token not found
} elseif ($record->revoked_at) {
    // Token revoked
} elseif ($record->expires_at && $record->expires_at < now()) {
    // Token expired
}
```

### Issue 5: Stored Procedure Not Found
**Cause:** Migration not run or procedure dropped.
**Solution:**
```bash
# Check if procedure exists
php artisan tinker
DB::select("SHOW PROCEDURE STATUS WHERE Name = 'sp_create_account'");

# Re-run procedure migrations
php artisan migrate:refresh --path=database/migrations/2026_02_10_200001_create_sp_create_account_procedure.php
```

---

## STEP 9: SECURITY CHECKLIST

Before going live, verify:

- [ ] Portal database user (`portal_rw`) has NO direct table access
- [ ] Portal user can only execute stored procedures for writes
- [ ] Portal user can only SELECT from views (account_safe_view, user_profile_view, api_tokens_view)
- [ ] RED tables (account_flags, auth_audit_log) are NOT accessible to portal user
- [ ] All API endpoints validate tenant_id from auth context (never from user input)
- [ ] All Eloquent queries include ->where('tenant_id', auth()->user()->tenant_id)
- [ ] Password hashes are NEVER exposed in API responses
- [ ] API token hashes are NEVER exposed in API responses (prefix only)
- [ ] CSRF protection enabled for all state-changing requests
- [ ] Rate limiting configured (60 requests/minute for API)
- [ ] Database triggers enforce tenant_id NOT NULL for all tenant-scoped tables
- [ ] Soft deletes enabled (deleted_at) - data never truly deleted for audit trail
- [ ] All authentication events logged to auth_audit_log
- [ ] All API token operations logged to auth_audit_log
- [ ] All routing changes logged to routing_audit_log
- [ ] All rate card changes logged to rate_card_audit_log

---

## STEP 10: DEPLOYMENT VERIFICATION SCRIPT

Run this script to verify everything is working:

```bash
#!/bin/bash

echo "QuickSMS Database Deployment Verification"
echo "=========================================="

cd /home/user/laravel-replit-template/package

# 1. Check database connection
echo "1. Testing database connection..."
php artisan db:show || { echo "FAIL: Cannot connect to database"; exit 1; }
echo "✓ Database connection OK"

# 2. Count migrations
echo "2. Checking migrations..."
MIGRATION_COUNT=$(php artisan migrate:status | grep -c "Ran")
echo "✓ $MIGRATION_COUNT migrations ran successfully"

# 3. Count tables
echo "3. Checking tables..."
TABLE_COUNT=$(php artisan tinker --execute="echo count(DB::select('SHOW TABLES'));" 2>/dev/null)
echo "✓ $TABLE_COUNT tables created"

# 4. Check critical tables exist
echo "4. Verifying critical tables..."
for table in accounts users api_tokens account_flags auth_audit_log routing_rules suppliers
do
  php artisan tinker --execute="DB::table('$table')->count();" 2>/dev/null || { echo "FAIL: Table $table missing"; exit 1; }
  echo "  ✓ $table exists"
done

# 5. Check views exist
echo "5. Verifying views..."
for view in account_safe_view user_profile_view api_tokens_view
do
  php artisan tinker --execute="DB::table('$view')->count();" 2>/dev/null || { echo "FAIL: View $view missing"; exit 1; }
  echo "  ✓ $view exists"
done

# 6. Check stored procedures exist
echo "6. Verifying stored procedures..."
for proc in sp_create_account sp_authenticate_user sp_create_api_token sp_update_user_profile sp_update_account_settings
do
  PROC_EXISTS=$(php artisan tinker --execute="echo count(DB::select(\"SHOW PROCEDURE STATUS WHERE Name = '$proc'\"));" 2>/dev/null)
  if [ "$PROC_EXISTS" -eq "0" ]; then
    echo "FAIL: Procedure $proc missing"
    exit 1
  fi
  echo "  ✓ $proc exists"
done

# 7. Test account creation
echo "7. Testing account creation..."
php artisan tinker --execute="
  \$account = App\Models\Account::create([
    'company_name' => 'Test Deployment',
    'email' => 'test@deployment.local',
    'country' => 'GB'
  ]);
  echo 'Account created: ' . \$account->account_number;
  \$account->delete();
" 2>/dev/null || { echo "FAIL: Cannot create account"; exit 1; }
echo "✓ Account creation OK"

echo ""
echo "=========================================="
echo "✓ ALL CHECKS PASSED - DEPLOYMENT SUCCESSFUL"
echo "=========================================="
```

Save as `verify_deployment.sh` and run:
```bash
chmod +x verify_deployment.sh
./verify_deployment.sh
```

---

## SUMMARY

You now have:
- ✅ Complete multi-tenant database schema (33 tables + views + procedures)
- ✅ RED/GREEN data separation for security
- ✅ Tenant isolation with UUID BINARY(16) keys
- ✅ 5-section account activation flow
- ✅ Authentication with MFA and mobile verification
- ✅ API token management with hash storage
- ✅ Routing rules for SMS gateway selection
- ✅ Supplier rate card management
- ✅ Comprehensive audit logging
- ✅ Stored procedures for critical business logic
- ✅ Views for portal-safe data projection

**Next Steps:**
1. Connect frontend UI to API endpoints
2. Test signup → activation → login flow end-to-end
3. Implement remaining UI screens (API keys, users, settings)
4. Configure database roles and grants (portal_ro, portal_rw, svc_red)
5. Set up database backups and monitoring
6. Configure rate limiting and abuse detection
7. Deploy to production with SSL/TLS

**Questions or Issues?**
If deployment fails or you encounter errors, check:
- Database credentials in `.env`
- Migration status: `php artisan migrate:status`
- Laravel logs: `storage/logs/laravel.log`
- Database error logs: Check MariaDB/MySQL error log

# Replit Prompt: Deploy QuickSMS Platform

## READ THIS ENTIRE PROMPT BEFORE DOING ANYTHING

You are deploying the **QuickSMS** platform — a UK enterprise multi-tenant SaaS for business messaging (SMS, RCS, WhatsApp Business). This is a security-critical application with ISO27001, Cyber Essentials Plus, and NHS DSP Toolkit compliance requirements.

**Your job is to deploy what exists. Not to improve it, refactor it, or "fix" things you think look wrong.**

---

## ANTI-DRIFT GUARDRAILS — READ BEFORE EVERY ACTION

### ABSOLUTE RULES — VIOLATION OF ANY RULE MEANS STOP AND ASK

#### DO NOT MODIFY
- Do NOT refactor, rename, reorganize, or "improve" any existing file
- Do NOT change variable names, method names, class names, or formatting
- Do NOT add comments, docblocks, type hints, or annotations to existing code
- Do NOT remove comments, whitespace, or blank lines from existing code
- Do NOT change the order of methods, properties, or route definitions
- Do NOT "fix" code style, PSR compliance, or linting warnings
- Do NOT add try/catch blocks, null checks, or validation that isn't already there
- Do NOT change any database migration files
- Do NOT change any stored procedure definitions
- Do NOT change any RLS policy definitions
- Do NOT change any model files (especially UUID handling or password hashing)
- Do NOT modify `Kernel.php` middleware registration or ordering
- Do NOT modify `SetTenantContext.php` middleware
- Do NOT modify `AuthController.php` or any authentication flow
- Do NOT modify `composer.json` or `package.json` dependencies

#### DO NOT CREATE
- Do NOT create new files not required for deployment
- Do NOT create README files, documentation, or guides
- Do NOT create test files, middleware, service providers, or helper classes
- Do NOT create new database migrations
- Do NOT create new routes

#### DO NOT DELETE
- Do NOT delete any existing files, routes, views, controllers, or models
- Do NOT delete any migration files, even if they look like duplicates
- Do NOT delete any RLS policies or stored procedures

#### DO NOT RUN (unless explicitly listed in a step below)
- Do NOT run `php artisan migrate:fresh` — this destroys all data
- Do NOT run `php artisan migrate:rollback` — this destroys schema
- Do NOT run `composer update` — only `composer install`
- Do NOT run `npm update` — only `npm install`
- Do NOT change database role passwords without explicit instruction
- Do NOT run any `DROP TABLE`, `DROP FUNCTION`, `DROP POLICY` SQL commands

### SPECIFIC DRIFT PATTERNS TO REJECT

These are changes that AI agents commonly introduce during deployment. **Reject ALL of them:**

1. **Adding UUID mutators** — `getIdAttribute()`, `setIdAttribute()`, `bin2hex()`, `hex2bin()` — these were intentionally removed. PostgreSQL uses native 36-char UUID strings.
2. **Adding password hashing to User model boot()** — password is hashed ONCE in the controller only. The User model must NOT auto-hash.
3. **Removing `SetTenantContext` from Kernel.php** — this middleware MUST remain in the `api` middleware group.
4. **Adding `OR current_setting(...) IS NULL` to RLS policies** — this was an intentional security fix. NULL context = zero rows, not all rows.
5. **Changing `SELECT * FROM sp_function()` to `CALL sp_function()`** — PostgreSQL functions use SELECT, not CALL. These are functions, not procedures.
6. **Replacing stored procedure calls with direct Eloquent queries** — account creation MUST use `sp_create_account()`, auth MUST use `sp_authenticate_user()`.
7. **Removing `FORCE ROW LEVEL SECURITY`** — this is required even when connected as table owner.
8. **Connecting as postgres/superuser** — the app MUST connect as `portal_rw` (customer-facing) or `svc_red` (admin). Never superuser.
9. **Adding `daily_message_limit` or `dailyLimit`** — this was intentionally removed from the email-to-sms feature.
10. **Removing `ucfirst()` from status transforms** — status values MUST use `ucfirst()` everywhere.
11. **Moving routes from `web.php` into `api.php`** — route file assignments are intentional.
12. **Adding SET ROLE or connection switching to controllers** — DB role handling is done at the connection config level, not per-controller.
13. **Creating FormRequest classes** — validation stays inline in controllers for this project.
14. **Adding `.env` values to version control** — secrets stay in Replit Secrets, never in committed files.

---

## ARCHITECTURE OVERVIEW (Context Only — Do Not Change)

### RED/GREEN Trust Boundary

```
GREEN (Customer-Facing)              RED (Internal/Admin)
========================             ========================
Customer Portal UI                   Admin Console UI
  |                                    |
  v                                    v
API routes (auth:sanctum)            Admin routes (admin auth)
  |                                    |
  v                                    v
SetTenantContext middleware           No tenant context
  |                                    |
  v                                    v
PostgreSQL (portal_rw role)          PostgreSQL (svc_red role)
RLS enforced per tenant              RLS bypassed for admin
```

### Database Roles
| Role | Purpose | RLS |
|------|---------|-----|
| `portal_ro` | Read-only portal access | Enforced |
| `portal_rw` | Read-write portal access (Laravel .env) | Enforced |
| `svc_red` | Internal/admin services | Bypassed |
| `ops_admin` | Operations admin | Full access |

### Security Rules (Non-Negotiable)
1. `tenant_id` always from authenticated session, never from request input
2. Account creation always via `sp_create_account()` stored procedure
3. Login always via `sp_authenticate_user()` stored procedure
4. Password hashed once in controller only — never in model
5. `SetTenantContext` middleware must remain in `Kernel.php` api group
6. All portal API responses use `toPortalArray()` — never raw model data
7. CSRF tokens included in all form submissions
8. All admin actions logged to `auth_audit_log`
9. `FORCE ROW LEVEL SECURITY` active on all 7+ tenant tables
10. The `accounts_isolation` RLS policy must NOT include a NULL-context bypass

---

## DEPLOYMENT STEPS

### Step 1: Verify Branch

```bash
git status
git branch
```

**Expected:** You should be on `main` or the deployment branch. If not, ask before switching.

### Step 2: Install PHP Dependencies

```bash
composer install --no-interaction --optimize-autoloader --no-dev
```

**Do NOT run `composer update`.** We install from the lockfile only.

### Step 3: Install Node Dependencies & Build Assets

```bash
npm install
npm run build
```

**Do NOT run `npm update`.** We install from the lockfile only.

### Step 4: Environment Configuration

Check that `.env` exists and has correct values:

```bash
# Verify .env exists
ls -la .env

# If .env doesn't exist, copy from example:
cp .env.example .env
php artisan key:generate --force
```

**Required .env values** (set via Replit Secrets or .env):

```env
APP_NAME=QuickSMS
APP_ENV=production
APP_DEBUG=false
APP_URL=<your-replit-url>

DB_CONNECTION=pgsql
DB_HOST=<replit-postgres-host>
DB_PORT=5432
DB_DATABASE=<database-name>
DB_USERNAME=portal_rw
DB_PASSWORD=<strong-password-from-secrets>

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

**CRITICAL CHECKS:**
- `APP_DEBUG` MUST be `false` in production
- `DB_USERNAME` MUST be `portal_rw` — NEVER `postgres` or superuser
- `APP_KEY` must be set (generated above if missing)

### Step 5: Database Setup

#### 5a. Verify PostgreSQL is accessible

```bash
php artisan db:monitor --databases=pgsql
```

#### 5b. Run the roles & grants script (FIRST TIME ONLY)

**This only runs once, when setting up a fresh database. Skip if roles already exist.**

```bash
# Check if roles already exist:
psql -d $DB_DATABASE -c "SELECT rolname FROM pg_roles WHERE rolname IN ('portal_ro','portal_rw','svc_red','ops_admin');"
```

If roles don't exist:
```bash
psql -d $DB_DATABASE -f package/database/setup/01_create_roles_and_grants.sql
```

Then set strong passwords (use Replit Secrets):
```sql
ALTER ROLE portal_rw WITH PASSWORD '<from-replit-secrets>';
ALTER ROLE svc_red WITH PASSWORD '<from-replit-secrets>';
```

#### 5c. Run migrations

```bash
php artisan migrate --force
```

**Do NOT use `migrate:fresh` — that destroys all data.**

#### 5d. Verify migration success

```bash
php artisan migrate:status
```

All migrations should show "Ran".

#### 5e. Verify RLS is active

```bash
psql -d $DB_DATABASE -c "
SELECT tablename, rowsecurity
FROM pg_tables
WHERE schemaname = 'public'
AND tablename IN ('accounts','users','user_sessions','api_tokens',
                   'email_verification_tokens','account_settings','account_credits');
"
```

**Expected:** All 7 tables show `rowsecurity = true`.

#### 5f. Verify FORCE RLS is active

```bash
psql -d $DB_DATABASE -c "
SELECT relname, relforcerowsecurity
FROM pg_class
WHERE relname IN ('accounts','users','user_sessions','api_tokens',
                   'email_verification_tokens','account_settings','account_credits');
"
```

**Expected:** All show `relforcerowsecurity = true`.

#### 5g. Verify stored procedures exist

```bash
psql -d $DB_DATABASE -c "
SELECT routine_name FROM information_schema.routines
WHERE routine_schema = 'public' AND routine_name LIKE 'sp_%';
"
```

**Expected:** 5 functions: `sp_create_account`, `sp_authenticate_user`, `sp_update_user_profile`, `sp_create_api_token`, `sp_update_account_settings`.

### Step 6: Seed Database (FIRST TIME ONLY)

```bash
php artisan db:seed --force
```

**Only run this on a fresh database. Running on an existing database may create duplicate records.**

### Step 7: Storage & Permissions

```bash
php artisan storage:link --force
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Step 8: Cache Configuration (Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 9: Verify Application Starts

```bash
php artisan serve --host=0.0.0.0 --port=5000 &
sleep 3
curl -s -o /dev/null -w "%{http_code}" http://localhost:5000
```

**Expected:** HTTP 200 or 302 (redirect to login).

Kill the test server:
```bash
kill %1 2>/dev/null
```

### Step 10: Final Verification Checklist

Run these checks and report results:

```bash
echo "=== DEPLOYMENT VERIFICATION ==="

echo "1. PHP version:"
php -v | head -1

echo "2. Laravel version:"
php artisan --version

echo "3. Database connection:"
php artisan db:monitor --databases=pgsql

echo "4. Migration status (last 5):"
php artisan migrate:status 2>&1 | tail -5

echo "5. Route count:"
php artisan route:list 2>&1 | wc -l

echo "6. APP_DEBUG setting:"
php artisan tinker --execute="echo config('app.debug') ? 'WARNING: DEBUG IS ON' : 'OK: Debug is off';"

echo "7. DB connection user:"
php artisan tinker --execute="echo DB::select('SELECT current_user')[0]->current_user;"

echo "8. APP_ENV:"
php artisan tinker --execute="echo config('app.env');"
```

**Expected results:**
- PHP 8.1+
- Laravel 10.x
- Database connected
- All migrations ran
- Routes registered
- Debug OFF in production
- DB user is `portal_rw` (NOT postgres)
- APP_ENV is `production`

---

## TROUBLESHOOTING

### "SQLSTATE connection refused"
PostgreSQL may not be ready yet. Wait and retry:
```bash
sleep 5 && php artisan migrate --force
```

### "Permission denied for table X"
The DB user doesn't have grants. Run the roles & grants script (Step 5b).

### "Column does not exist" errors
Migrations may not have run completely. Check:
```bash
php artisan migrate:status
```
Run pending migrations:
```bash
php artisan migrate --force
```

### "Class not found" errors
Autoloader needs rebuilding:
```bash
composer dump-autoload --optimize
```

### Cache issues after config changes
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

## POST-DEPLOYMENT — DO NOT DO THESE

After deployment is complete:
- Do NOT "optimize" or "refactor" anything
- Do NOT add monitoring, logging, or health-check endpoints that don't already exist
- Do NOT modify the Blade templates
- Do NOT change the authentication flow
- Do NOT create new files
- Do NOT modify the `.replit` file or `replit.nix`
- Do NOT modify `setup.sh`

**Your deployment is complete. Report the verification results and stop.**

---

## REPLIT-SPECIFIC CONFIGURATION

The following files control how Replit runs the application. **Do not modify them.**

### `.replit` (already configured)
- Modules: PHP 8.3, Node.js 20, PostgreSQL 16
- Boot: `bash setup.sh`
- Run: `php artisan serve --host=0.0.0.0 --port=5000`
- Port: 5000 (mapped to external port 80)
- Deployment target: autoscale

### `replit.nix` (already configured)
- PHP 8.3 with extensions: pgsql, pdo_pgsql, mbstring, xml, curl, gd, zip
- Composer, Node.js 20, unzip, git

### `setup.sh` (already configured)
- Runs on boot: composer install, .env setup, key generation, storage setup, migrations, seeding
- Has PostgreSQL readiness retry loop (15 attempts, 2s apart)

### Replit Secrets (must be configured in Replit UI)
These values should be set as Replit Secrets (not in .env or committed files):
- `DB_PASSWORD` — password for the `portal_rw` database role
- `APP_KEY` — Laravel application encryption key
- `STRIPE_KEY` — Stripe API public key (if billing is active)
- `STRIPE_SECRET` — Stripe API secret key (if billing is active)

### Deployment Target
The `.replit` file is configured for `autoscale` deployment:
```toml
[deployment]
deploymentTarget = "autoscale"
build = ["bash", "setup.sh"]
run = ["php", "artisan", "serve", "--host=0.0.0.0", "--port=5000"]
```

This means Replit handles scaling automatically. The `setup.sh` script runs during build, and the artisan server command is the run target.

---

## IF SOMETHING GOES WRONG

1. **Do NOT try to fix it by modifying application code**
2. **Do NOT create workaround scripts or patches**
3. **Do NOT modify migrations, models, or controllers**
4. Report exactly what error you see, which step failed, and the full error message
5. Ask before taking any corrective action

**The codebase has been through a full Opus-level security review. All 8 identified ship-blockers have been fixed. The deployment should work as-is. If it doesn't, the issue is environmental (database access, missing secrets, permissions), not code.**

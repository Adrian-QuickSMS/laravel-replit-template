# PULL REQUEST: SMS Country Permission System

## INSTRUCTION TO REPLIT AGENT

Pull branch `claude/sms-country-permissions-nDoBK` and merge it into your current working branch. Then run the 3 new migrations.

```bash
git fetch origin claude/sms-country-permissions-nDoBK
git merge origin/claude/sms-country-permissions-nDoBK --no-edit
php artisan migrate
```

That is the ONLY task. Everything below is context for verification. Do NOT write any code.

---

## GUARDRAILS — READ BEFORE DOING ANYTHING

### DO
- Pull the branch and merge it
- Run `php artisan migrate` to apply the 3 new migrations
- Verify the merge completed without conflicts
- Report back what happened

### DO NOT
- Do NOT modify, rewrite, refactor, or "improve" any of the pulled files
- Do NOT create new files, services, controllers, models, or migrations
- Do NOT change any existing file beyond what the merge introduces
- Do NOT add tests, comments, documentation, or type hints
- Do NOT rename anything, move anything, or restructure anything
- Do NOT "fix" code you think looks wrong — it has already been reviewed and fixed
- Do NOT add feature flags, config files, or environment variables
- Do NOT touch routes beyond what the merge brings in
- Do NOT create views or blade templates — this is backend only
- Do NOT run any destructive git commands (reset, force push, rebase)

### IF MERGE CONFLICTS OCCUR
- Report the conflicting files and stop
- Do NOT resolve conflicts yourself — report them for manual resolution

---

## WHAT THIS FEATURE CONTAINS (verification reference only)

### 4 commits being merged:
1. `004f8dd` — Core country permission system with tiered caching and sub-account support
2. `2a4951d` — Bug fixes: RLS policy, Redis prefix, type mismatch, fallback handling
3. `76448e8` — Fix N+1 country name lookups in campaign validation
4. `cfe2c82` — Fix static property leakage, prevent caching empty on DB failure, null-safe auth

### 17 files changed (1,049 additions, 4 deletions):

**New files (8):**
- `app/Services/CountryPermissionCacheService.php` — L1/L2/L3 cache (in-process → Redis → PostgreSQL)
- `app/Services/CountryPermissionCheckService.php` — Phone number → country resolution and permission enforcement
- `app/Http/Controllers/Admin/SubAccountCountryPermissionController.php` — Admin CRUD for sub-account overrides
- `app/Models/SubAccountCountryPermission.php` — Eloquent model
- `app/Jobs/WarmCountryPermissionCache.php` — Background cache warming job
- `database/migrations/2026_03_13_170000_create_sub_account_country_permissions_table.php`
- `database/migrations/2026_03_13_171000_add_rls_to_sub_account_country_permissions.php`
- `database/migrations/2026_03_13_172000_fix_created_by_type_on_sub_account_country_permissions.php`

**Modified files (9):**
- `app/Providers/AppServiceProvider.php` — Singleton registration for cache + check services
- `app/Services/Campaign/CampaignService.php` — Country permission enforcement in validateForSend()
- `app/Http/Controllers/QuickSMSController.php` — 2 new customer API endpoints + security page enrichment
- `app/Http/Controllers/Admin/CountryControlController.php` — Cache invalidation on global/account changes
- `app/Http/Controllers/Admin/ApprovalQueueController.php` — Cache invalidation on country request approval
- `app/Models/CountryControl.php` — Relationship + query scopes
- `app/Models/CountryControlOverride.php` — Casts added
- `app/Models/SubAccount.php` — countryPermissions relationship
- `routes/web.php` — 6 new routes (2 customer, 4 admin)

### 3 migrations to run:
1. Creates `sub_account_country_permissions` table with foreign keys and unique constraint
2. Enables Row Level Security with tenant isolation policy
3. Changes `created_by` column from UUID to VARCHAR(255) for admin email storage

### New routes added:

**Customer routes (inside existing auth middleware):**
- `GET /api/country-permissions` — List effective permissions for current account
- `POST /api/country-permissions/check` — Check if a phone number destination is allowed

**Admin routes (inside existing AdminAuthenticate + AdminIpAllowlist middleware):**
- `GET /api/sub-account-country-permissions/{subAccountId}` — List sub-account permissions
- `POST /api/sub-account-country-permissions` — Set a sub-account override
- `POST /api/sub-account-country-permissions/bulk` — Bulk set overrides
- `DELETE /api/sub-account-country-permissions/{permissionId}` — Remove an override

---

## POST-MERGE VERIFICATION

After the merge and migration, confirm:
1. `php artisan route:list | grep country-permission` shows the 6 new routes
2. The `sub_account_country_permissions` table exists in the database
3. No PHP syntax errors: `php artisan config:clear && php artisan route:clear`

Report the results. Do not take any further action.

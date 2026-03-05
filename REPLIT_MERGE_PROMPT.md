# Email-to-SMS Backend — Pull & Merge (Final)

Use this prompt when working with Replit Agent to pull and merge the `claude/quicksms-security-performance-dr8sw` branch into `main`.

---

## Prompt for Replit Agent

```
You are pulling the Email-to-SMS backend from a feature branch and merging it into main. Follow these rules STRICTLY. Do NOT deviate, improve, refactor, or "fix" anything.

## BRANCH INFO
- Source branch: claude/quicksms-security-performance-dr8sw
- Target branch: main

## FILE MANIFEST — ONLY THESE FILES ARE ALLOWED TO CHANGE

### NEW files (create only — do not modify after merge):
1. database/migrations/2026_03_05_000001_create_email_to_sms_tables.php
2. app/Models/EmailToSmsSetup.php
3. app/Models/EmailToSmsReportingGroup.php
4. app/Models/EmailToSmsAuditLog.php
5. app/Http/Controllers/Api/EmailToSmsController.php
6. app/Http/Controllers/Api/EmailToSmsReportingGroupController.php
7. app/Http/Controllers/Api/Admin/AdminEmailToSmsController.php

### MODIFIED files (merge changes only — do not rewrite):
8. routes/web.php — new route groups APPENDED, existing routes untouched
9. public/js/services/email-to-sms-service.js — useMockData=false, CSRF token, real API endpoints
10. resources/views/admin/assets/email-to-sms.blade.php — baseUrl override added

If the merge diff touches ANY file not listed above, STOP and report it. Do NOT proceed.

## ABSOLUTE RULES — VIOLATION OF ANY RULE MEANS STOP

### DO NOT MODIFY
- Do NOT refactor, rename, reorganize, or "improve" any merged file
- Do NOT change variable names, method names, class names, or formatting
- Do NOT add comments, docblocks, type hints, or annotations
- Do NOT remove comments, whitespace, or blank lines
- Do NOT change the order of methods, properties, or route definitions
- Do NOT "fix" code style, PSR compliance, or linting warnings
- Do NOT add try/catch blocks, null checks, or validation that isn't already there

### DO NOT CREATE
- Do NOT create new files not in the manifest (no READMEs, no configs, no tests, no middleware)
- Do NOT create Form Request classes, service providers, or resource classes
- Do NOT add or modify any config/ files
- Do NOT add or modify .env, .env.example, composer.json, or package.json
- Do NOT add or modify any Blade views except email-to-sms.blade.php

### DO NOT DELETE
- Do NOT delete any existing routes in routes/web.php
- Do NOT delete any existing code in files being modified
- Do NOT remove any migration columns, indexes, or RLS policies

### DO NOT RUN
- Do NOT run php artisan migrate (migration will be run manually)
- Do NOT run composer install/update or npm install/update
- Do NOT run php artisan optimize, config:cache, or route:cache

### SPECIFIC DRIFT PATTERNS TO REJECT
These are changes that AI agents commonly introduce during merges. Reject ALL of them:

- Adding a daily_message_limit or dailyLimit field anywhere — this was intentionally removed
- Adding an account_flags query to the overview endpoint — this was intentionally removed
- Changing ucfirst($setup->status) back to $setup->status — status must use ucfirst() everywhere
- Removing the /contact-list-setups/* route aliases — these are required for JS service compatibility
- Moving routes from web.php into api.php or a separate routes file
- Creating a FormRequest class for setup validation
- Wrapping the accountFlags() method in try/catch — it queries account_settings which always exists
- Adding a daily_limit column to the email_to_sms_setups migration
- Changing NULLIF(current_setting('app.current_tenant_id', true), '')::uuid in RLS policies — this matches the project-wide pattern
- Adding SET ROLE or connection switching to the admin controller — admin DB role handling is a project-wide architecture decision, not this feature's scope

## MERGE PROCEDURE

Step 1: Fetch and merge
```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git checkout main
git pull origin main
git merge origin/claude/quicksms-security-performance-dr8sw --no-ff -m "Merge email-to-sms backend: models, migrations, controllers, routes, JS service"
```

Step 2: If merge conflicts exist in routes/web.php or the JS service, resolve by KEEPING BOTH sides — existing main code AND new additions. Do not drop either side.

Step 3: Verify the merge — run this and check output:
```bash
git diff main~1..main --name-only
```
Expected output should ONLY contain the 10 files listed in the manifest above (plus the two REPLIT_*.md prompt files which can be ignored). If unexpected files appear, STOP.

Step 4: Syntax check all PHP files:
```bash
php -l database/migrations/2026_03_05_000001_create_email_to_sms_tables.php
php -l app/Models/EmailToSmsSetup.php
php -l app/Models/EmailToSmsReportingGroup.php
php -l app/Models/EmailToSmsAuditLog.php
php -l app/Http/Controllers/Api/EmailToSmsController.php
php -l app/Http/Controllers/Api/EmailToSmsReportingGroupController.php
php -l app/Http/Controllers/Api/Admin/AdminEmailToSmsController.php
```
All must return "No syntax errors detected". If any fail, STOP.

Step 5: Verify routes parse correctly:
```bash
php artisan route:list --path=email-to-sms 2>&1 | head -40
```
You should see routes for: overview, setups CRUD, contact-list-setups aliases, reporting-groups CRUD, templates/senderids, subaccounts, account/flags, and admin endpoints.

Step 6: Push to main:
```bash
git push origin main
```

## SECURITY FEATURES — DO NOT WEAKEN OR REMOVE

These are intentional security measures in the merged code. Do not "simplify" them:

1. **Row Level Security (RLS)** — All 3 tables (email_to_sms_setups, email_to_sms_reporting_groups, email_to_sms_audit_log) have ENABLE + FORCE ROW LEVEL SECURITY with tenant isolation policies
2. **forAccount() scope** — All customer controller queries use EmailToSmsSetup::forAccount($this->tenantId()) for application-level tenant isolation on top of RLS
3. **Cross-tenant validation** — store() and update() verify sub_account_id and reporting_group_id belong to the tenant before saving
4. **DB::transaction()** — All create/update mutations in customer controllers are wrapped in transactions with audit logging
5. **Unique constraints** — Both account+name uniqueness (DB level) and duplicate-name checks (application level)
6. **Email collision protection** — Generated email addresses use hash + retry loop with unique DB constraint
7. **Audit logging** — Every state change (create, update, suspend, reactivate, archive, delete) is logged via EmailToSmsAuditLog

## WHAT THE CODE DOES (for context only — do not use this to "improve" anything)

### Customer API (portal_rw role, tenant-scoped):
- GET  /api/email-to-sms/overview — unified listing of all setups with message counts
- GET  /api/email-to-sms/setups — list setups (filterable by type, status, sub_account)
- POST /api/email-to-sms/setups — create new setup (standard or contact_list)
- GET  /api/email-to-sms/setups/{id} — show single setup
- PUT  /api/email-to-sms/setups/{id} — update setup
- DELETE /api/email-to-sms/setups/{id} — soft delete setup
- POST /api/email-to-sms/setups/{id}/suspend|reactivate|archive|unarchive — state transitions
- GET  /api/email-to-sms/contact-list-setups — alias for /setups?type=contact_list (JS service compat)
- GET  /api/email-to-sms/reporting-groups — list, POST to create, PUT/{id} to update, etc.
- GET  /api/email-to-sms/templates/senderids — approved sender IDs for this account
- GET  /api/email-to-sms/subaccounts — active sub-accounts for this account
- GET  /api/email-to-sms/account/flags — feature flags (dynamic senderid, limits)

### Admin API (cross-tenant, admin auth required):
- GET  /admin/api/email-to-sms/overview — cross-account setup listing
- GET  /admin/api/email-to-sms/accounts — accounts with email-to-sms setups
- GET  /admin/api/email-to-sms/reporting-groups — all reporting groups
- GET/PUT /admin/api/email-to-sms/setups/{id} — view/edit any setup
- POST /admin/api/email-to-sms/setups/{id}/suspend|reactivate — admin state changes
- DELETE /admin/api/email-to-sms/setups/{id} — admin delete

## POST-MERGE CHECKLIST

After merge, manually verify:
- [ ] `php artisan route:list --path=email-to-sms` shows all expected routes
- [ ] No PHP syntax errors in any merged file
- [ ] No existing routes were removed or modified
- [ ] The JS service file has `useMockData: false` (not true)
- [ ] The migration file date is 2026_03_05_000001
- [ ] No dailyLimit field appears anywhere in EmailToSmsController.php
- [ ] No account_flags query appears in the overview() method
- [ ] Status fields use ucfirst() in ALL transform methods and overview responses
```

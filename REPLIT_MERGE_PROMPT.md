# Email-to-SMS Backend — Merge & Deploy Guardrails

Use this prompt when working with Replit Agent to merge the `claude/quicksms-security-performance-dr8sw` branch into `main`.

---

## Prompt for Replit Agent

```
You are merging the Email-to-SMS backend feature branch into main. Follow these rules STRICTLY. Do NOT deviate.

## BRANCH INFO
- Source branch: claude/quicksms-security-performance-dr8sw
- Target branch: main

## ABSOLUTE RULES — DO NOT BREAK THESE

1. **DO NOT modify any file that was not changed in the source branch.** Only these files were changed or created:
   - database/migrations/2026_03_05_000001_create_email_to_sms_tables.php (NEW)
   - app/Models/EmailToSmsSetup.php (NEW)
   - app/Models/EmailToSmsReportingGroup.php (NEW)
   - app/Http/Controllers/Api/EmailToSmsController.php (NEW)
   - app/Http/Controllers/Api/EmailToSmsReportingGroupController.php (NEW)
   - app/Http/Controllers/Api/Admin/AdminEmailToSmsController.php (NEW)
   - routes/web.php (MODIFIED — routes added at end of file)
   - public/js/services/email-to-sms-service.js (MODIFIED — useMockData + CSRF + endpoints)
   - resources/views/admin/assets/email-to-sms.blade.php (MODIFIED — baseUrl override added)

2. **DO NOT refactor, rename, reorganize, or "improve" any of these files.** Merge them exactly as they are. No style changes, no "cleanup", no adding comments, no removing comments.

3. **DO NOT create new files** that are not in the source branch. No README updates, no config changes, no new middleware, no new service providers.

4. **DO NOT delete or modify existing routes** in routes/web.php. The branch ONLY APPENDS new route groups. Preserve every existing line.

5. **DO NOT change the migration filename or date prefix.** It must remain `2026_03_05_000001_create_email_to_sms_tables.php`.

6. **DO NOT run `php artisan migrate` automatically.** Migration will be run manually after review.

7. **DO NOT modify any model, controller, or view outside the email-to-sms feature.** This includes:
   - DO NOT touch QuickSMSController.php
   - DO NOT touch AdminController.php
   - DO NOT touch any other blade views
   - DO NOT touch any other JS files
   - DO NOT touch composer.json or package.json
   - DO NOT touch .env or config files

8. **DO NOT add or remove any dependencies.** No composer require, no npm install.

## MERGE PROCEDURE

Step 1: Fetch the source branch
```
git fetch origin claude/quicksms-security-performance-dr8sw
```

Step 2: Checkout main and ensure it's up to date
```
git checkout main
git pull origin main
```

Step 3: Merge with a merge commit (no squash, no rebase)
```
git merge origin/claude/quicksms-security-performance-dr8sw --no-ff -m "Merge email-to-sms backend: models, migrations, API controllers, routes"
```

Step 4: If there are merge conflicts in routes/web.php or the JS service file, resolve by KEEPING BOTH — the existing main code AND the new additions. Do not drop either side.

Step 5: Verify the merge contains exactly the expected files
```
git diff main~1..main --name-only
```
This should list ONLY the 9 files above. If other files appear, STOP and report.

Step 6: Run syntax checks on all PHP files
```
php -l app/Models/EmailToSmsSetup.php
php -l app/Models/EmailToSmsReportingGroup.php
php -l app/Http/Controllers/Api/EmailToSmsController.php
php -l app/Http/Controllers/Api/EmailToSmsReportingGroupController.php
php -l app/Http/Controllers/Api/Admin/AdminEmailToSmsController.php
php -l database/migrations/2026_03_05_000001_create_email_to_sms_tables.php
```
All must return "No syntax errors detected". If any fail, STOP and report.

Step 7: Push to main
```
git push origin main
```

## POST-MERGE VERIFICATION

After pushing, confirm:
- [ ] `useMockData` is `false` in email-to-sms-service.js
- [ ] CSRF token header is present in `_makeRequest()` in email-to-sms-service.js
- [ ] Admin blade view sets `EmailToSmsService.config.baseUrl = '/admin/api/email-to-sms'`
- [ ] Customer routes are under `api/email-to-sms/` with `customer.auth` middleware
- [ ] Admin routes are under `admin/api/email-to-sms/` within admin middleware group
- [ ] Migration creates both `email_to_sms_reporting_groups` and `email_to_sms_setups` tables
- [ ] Both models use `SoftDeletes`, UUID PKs, and `scopeForAccount`

## WHAT TO DO IF SOMETHING GOES WRONG

If the merge introduces unexpected changes or breaks anything:
```
git reset --hard HEAD~1
git push origin main --force-with-lease
```
Then report what went wrong.

## EXPLICITLY FORBIDDEN ACTIONS

- Adding error handling or validation "improvements"
- Extracting code into service classes or repositories
- Adding Form Request classes
- Adding API resource classes
- Adding tests (these will be added separately)
- Modifying the database schema beyond what the migration defines
- Adding middleware or policies
- Changing PHP or JS coding style
- Adding TypeScript types or JSDoc comments
- Creating API documentation files
- Updating any CI/CD configuration
```

---

## Why These Guardrails Exist

The email-to-sms backend was built to match existing codebase patterns exactly:
- Controller style matches `ApiConnectionController.php`
- Model style matches `SubAccount.php`
- Route registration matches existing groups in `routes/web.php`
- JS service was already written — only the data source toggle and endpoints changed

Any "improvements" during merge will break pattern consistency and introduce drift that makes the codebase harder to maintain. Merge it clean, validate it works, then iterate in future branches.

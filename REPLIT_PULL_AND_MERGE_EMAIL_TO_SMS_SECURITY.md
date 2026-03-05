# Replit Merge Prompt: Email-to-SMS Security & Functional Fixes

## Copy everything below this line into Replit chat

---

Pull and merge the Email-to-SMS security and functional fixes from branch `claude/quicksms-security-performance-dr8sw`, commit `042a289`.

## What this commit does

This is a **security remediation and bug-fix commit** for the Email-to-SMS module. It fixes 12 verified issues across 6 files. There are **zero new features** — every change addresses a confirmed defect or security gap.

## Source branch and commit

```
Branch: claude/quicksms-security-performance-dr8sw
Commit: 042a289
Parent: b286659
```

## Exact files changed (6 files only)

```
MODIFIED (5):
  app/Http/Controllers/Api/Admin/AdminEmailToSmsController.php
  app/Http/Controllers/Api/EmailToSmsController.php
  app/Http/Controllers/Api/EmailToSmsReportingGroupController.php
  app/Models/EmailToSmsSetup.php
  database/migrations/2026_03_05_000001_create_email_to_sms_tables.php

NEW (1):
  app/Models/EmailToSmsAuditLog.php
```

## Pull commands

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git cherry-pick 042a289
```

If cherry-pick has conflicts, prefer the INCOMING (theirs) version for these 6 files — they contain the security fixes.

## What changed and why — issue-by-issue

### CRITICAL fixes

1. **RLS added to all tables** — `email_to_sms_setups`, `email_to_sms_reporting_groups`, and the new `email_to_sms_audit_log` table now have `ENABLE ROW LEVEL SECURITY`, `FORCE ROW LEVEL SECURITY`, and tenant isolation policies matching the `sender_ids` table pattern. Without this, tenant data leaks if middleware is bypassed.

2. **Audit log table created** — New `email_to_sms_audit_log` table with RLS. New `EmailToSmsAuditLog` model with `logAction()` static method. All controller mutations (create, update, suspend, reactivate, archive, unarchive, delete) now log to this table with user_id, IP, user_agent, and change payloads.

3. **SenderID lookup fixed** — `senderIdTemplates()` was querying `message_templates WHERE status = 'live'`. SenderIDs live in the `sender_ids` table and use `workflow_status`. Now queries `sender_ids WHERE workflow_status = 'approved'`. The old code would return wrong data or nothing.

4. **Unique constraint on setup names** — Added `UNIQUE(account_id, name)` to both `email_to_sms_setups` and `email_to_sms_reporting_groups` migrations. Added duplicate name validation in `store()` and `update()` methods for both controllers.

5. **Dead `subject_overrides_sender_id` removed** — This field was removed from the frontend but still existed in migration, model, and all controllers. Now removed from: migration column definition, model `$fillable` and `$casts`, `EmailToSmsController` (store/update/transform), `AdminEmailToSmsController` (update validation, update fields, transform).

### SIGNIFICANT fixes

6. **Email address uniqueness + collision protection** — Added `generated_email_address` column with a `UNIQUE` index. Email generation now uses a 7-char hash with `mt_rand()` entropy and retries up to 5 times on collision. Previously there was no unique index and no retry loop.

7. **Overview now returns expected fields** — The overview endpoint now includes `messagesSent` (from message_logs), `dailyLimit` (from account_flags), `optOut` (boolean), and `optOutMode` — all required by the frontend drawer component.

8. **accountFlags reads from database** — Was returning hardcoded `true` for everything. Now checks `account_settings` for delivery report preferences and queries `sender_ids` to determine if the account has approved sender IDs.

9. **DB transactions on create/update** — `store()` and `update()` in both controllers now wrap the record mutation + audit log write in `DB::transaction()`.

10. **Silent exception catching fixed** — The reporting group controller's `message_logs` query catch block was silently swallowing all exceptions. Now logs via `Log::warning()` so failures are visible.

11. **Type field now required** — Setup creation changed from `'type' => 'nullable|in:standard,contact_list'` to `'type' => 'required|in:standard,contact_list'` so callers must explicitly declare the setup type.

## STRICT GUARDRAILS — DO NOT VIOLATE

### DO NOT modify these files beyond what's in the commit:
- `routes/web.php` — no route changes in this commit
- `public/js/services/email-to-sms-service.js` — no JS changes
- Any blade templates
- Any other migration files
- Any files not listed in the 6 files above

### DO NOT:
- Add new features, endpoints, or UI changes
- Rename or restructure any existing endpoints
- Change the JSONB column design to normalised tables (that's a separate future task)
- Add Contact List-specific endpoints (the unified endpoint works)
- Add soft-delete recovery endpoints
- Modify the `EmailToSmsReportingGroup` model (it was not changed in this commit)
- Create new service classes, middleware, or helpers
- Run `php artisan migrate:fresh` — this is additive only
- Change any authentication or middleware configuration

### DO verify after merge:
1. The migration file has exactly 3 `Schema::create` calls: `email_to_sms_reporting_groups`, `email_to_sms_setups`, `email_to_sms_audit_log`
2. The migration has 6 `DB::unprepared` calls for RLS (2 per table: ENABLE + FORCE) plus 3 CREATE POLICY statements
3. `EmailToSmsSetup` model does NOT contain `subject_overrides_sender_id` in `$fillable` or `$casts`
4. `EmailToSmsController::senderIdTemplates()` queries `sender_ids` table (NOT `message_templates`)
5. `EmailToSmsController::store()` has `'type' => 'required|in:standard,contact_list'` (required, not nullable)
6. `EmailToSmsAuditLog.php` exists in `app/Models/`
7. All controller state-change methods call `EmailToSmsAuditLog::logAction()`
8. `email_to_sms_setups` has a `generated_email_address` column with unique index
9. No file outside the 6 listed files was modified

### If the migration has already been run:
Create a new migration to add the missing pieces rather than modifying the existing one:
```bash
php artisan make:migration add_security_fixes_to_email_to_sms_tables
```
Then manually add: RLS statements, audit_log table creation, unique constraints, generated_email_address column, and drop subject_overrides_sender_id column.

---

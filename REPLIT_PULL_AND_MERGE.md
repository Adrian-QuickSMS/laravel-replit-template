# Replit: Pull and Merge All Backend Changes from Claude Branch

## READ THIS ENTIRE FILE BEFORE DOING ANYTHING

This prompt tells you exactly how to pull the `claude/quicksms-security-performance-dr8sw` branch and merge it into your working branch. **Every file is listed. Every route is listed. Do not improvise, do not rewrite, do not "improve". Pull and apply exactly as described.**

---

## Step 1: Pull the Branch

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git merge origin/claude/quicksms-security-performance-dr8sw --no-edit
```

If there are merge conflicts, resolve them by **keeping the incoming (Claude branch) version** for all files listed in this document. Your local UI work in Blade templates should be preserved where it doesn't conflict.

After merge:

```bash
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear
php artisan migrate --force
```

---

## Step 2: Verify — What You Should Now Have

After merging, confirm these files exist and are not empty. **Do NOT modify, rename, refactor, or "clean up" any of them.**

### 2A. New Database Migrations (run in order)

These create all the tables the backend needs. They use raw PostgreSQL (`DB::statement`, `DB::unprepared`) for UUIDs, enums, triggers, and RLS. **Do not convert to MySQL syntax. Do not modify.**

| Migration | Purpose |
|---|---|
| `2026_02_24_000001_create_message_templates_table.php` | Message templates with content types (sms, rcs_basic, rcs_single, rcs_carousel) |
| `2026_02_24_000002_create_campaigns_table.php` | Campaign lifecycle (draft → queued → sending → completed), billing fields, scheduling |
| `2026_02_24_000003_create_campaign_recipients_table.php` | Per-recipient records with delivery status, cost, country |
| `2026_02_24_000004_create_media_library_table.php` | RCS media asset storage |
| `2026_02_24_100001_add_campaign_preparation_tracking.php` | Adds preparation_status to campaigns |
| `2026_02_25_000001_create_numbers_module_tables.php` | 5 tables: vmn_pool, purchased_numbers, shortcode_keywords, number_assignments, number_auto_reply_rules |
| `2026_02_25_000002_add_campaign_opt_out_fields.php` | Adds opt-out columns to campaigns table |
| `2026_02_27_000001_create_rcs_assets_table.php` | RCS uploaded/processed assets |
| `2026_03_02_000001_create_campaign_estimate_snapshots_table.php` | Immutable pricing snapshot frozen at send time (HMRC 7-year audit) |

### 2B. New Eloquent Models

Each model follows the existing pattern: UUID PK, `$incrementing = false`, `$keyType = 'string'`, global tenant scope, `toPortalArray()` method. **Do not change the model pattern.**

| Model File | Table |
|---|---|
| `app/Models/Campaign.php` | campaigns |
| `app/Models/CampaignRecipient.php` | campaign_recipients |
| `app/Models/MessageTemplate.php` | message_templates |
| `app/Models/MediaLibraryItem.php` | media_library |
| `app/Models/CampaignOptOutUrl.php` | campaign_opt_out_urls (virtual — uses campaign fields) |
| `app/Models/PurchasedNumber.php` | purchased_numbers |
| `app/Models/VmnPoolNumber.php` | vmn_pool |
| `app/Models/ShortcodeKeyword.php` | shortcode_keywords |
| `app/Models/NumberAssignment.php` | number_assignments |
| `app/Models/NumberAutoReplyRule.php` | number_auto_reply_rules |
| `app/Models/RcsAsset.php` | rcs_assets |
| `app/Models/Billing/CampaignEstimateSnapshot.php` | campaign_estimate_snapshots |

### 2C. New Service Classes

These are the backend business logic. They call existing services (PricingEngine, BalanceService, LedgerService). **Do not rewrite, do not simplify, do not merge into other classes.**

| Service File | Responsibility |
|---|---|
| `app/Services/Campaign/CampaignService.php` | Full campaign lifecycle: create, prepare, send, schedule, pause, resume, cancel, clone |
| `app/Services/Campaign/BillingPreflightService.php` | Pre-send balance check, cost estimation, fund reservation |
| `app/Services/Campaign/DeliveryService.php` | Message dispatch via gateway, delivery receipt handling |
| `app/Services/Campaign/RecipientResolverService.php` | Resolves recipients from CSV/lists, validates phones, removes opt-outs |
| `app/Services/Campaign/PhoneNumberUtils.php` | MSISDN normalisation, country detection, format validation |
| `app/Services/Campaign/ResolverResult.php` | DTO for resolver output |
| `app/Services/Numbers/NumberService.php` | VMN purchase from pool, keyword purchase, release, assignment |
| `app/Services/Numbers/NumberBillingService.php` | Number pricing, setup fee debit, recurring charge management |
| `app/Services/OptOutService.php` | Opt-out keyword validation, available numbers/keywords for campaigns, text generation |
| `app/Services/RcsContentValidator.php` | Validates RCS card/carousel structure, button limits, content lengths |
| `app/Services/RcsAssetService.php` | Image upload, processing, cropping, finalization for RCS |

### 2D. New Queue Jobs

| Job File | Responsibility |
|---|---|
| `app/Jobs/ProcessCampaignBatch.php` | Processes a batch of campaign recipients (sends messages) |
| `app/Jobs/ResolveRecipientContentJob.php` | Resolves per-recipient placeholders and content |
| `app/Jobs/ScheduledCampaignDispatcher.php` | Cron-driven: dispatches campaigns when scheduled_at arrives |

### 2E. New API Controllers

| Controller File | Responsibility |
|---|---|
| `app/Http/Controllers/Api/CampaignApiController.php` | Full campaign JSON API (CRUD, prepare, estimate, send, schedule, clone) |
| `app/Http/Controllers/Api/MessageTemplateApiController.php` | Message template CRUD + favourite toggle |
| `app/Http/Controllers/Api/NumberApiController.php` | Numbers module: library, pool, purchase VMN/keyword, assign, auto-reply |
| `app/Http/Controllers/OptOutLandingController.php` | Public opt-out landing page (no auth required) |

### 2F. Modified Controllers

| Controller | What Changed |
|---|---|
| `app/Http/Controllers/QuickSMSController.php` | Added: `confirmAndSend()`, `accountPricingApi()`, RCS agent pre-population, real balance/pricing on dashboard & confirm page |
| `app/Http/Controllers/Api/RcsAssetController.php` | Minor security fix |

### 2G. New Config & Provider Files

| File | Purpose |
|---|---|
| `config/billing.php` | Billing configuration (VAT rate, currency, reservation TTL, retry limits) |
| `app/Providers/BillingServiceProvider.php` | Registers billing services in the container |
| `app/Providers/RouteServiceProvider.php` | Updated to load `api_billing.php` route file |

### 2H. New Route File

| File | Purpose |
|---|---|
| `routes/api_billing.php` | All billing API routes (customer v1 + admin v1 + webhooks) |

### 2I. New Frontend Files

| File | Purpose |
|---|---|
| `public/js/security-helpers.js` | CSRF token helper, XSS sanitisation utility |
| `resources/views/optout/landing.blade.php` | Public opt-out page |
| `resources/views/optout/confirmed.blade.php` | Opt-out confirmation page |
| `resources/views/optout/invalid.blade.php` | Invalid opt-out token page |

### 2J. Updated Blade Template

| File | What Changed |
|---|---|
| `resources/views/quicksms/messages/confirm-campaign.blade.php` | Uses real backend cost estimate, shows insufficient balance warnings, POSTs to `/messages/confirm-send`, handles JSON error responses |

### 2K. Setup Script

| File | Purpose |
|---|---|
| `setup.sh` | Bootstraps Laravel in Replit: composer install, .env config, key gen, storage link, migrations, seeding |

---

## Step 3: Verify Routes

**CRITICAL: The route structure has been significantly refactored. Do not reorganise routes. Do not move routes between files. Do not rename route URIs.**

### `routes/web.php` — Must contain these route groups:

#### Public (no auth):
```
GET  /o/{token}          → OptOutLandingController@show
POST /o/{token}/confirm   → OptOutLandingController@confirm
```

#### Customer Portal (middleware: customer.auth, throttle:60,1):

**Campaign / Send Message APIs:**
```
GET    /api/campaigns                         → CampaignApiController@index
POST   /api/campaigns                         → CampaignApiController@store
GET    /api/campaigns/{id}                    → CampaignApiController@show
PUT    /api/campaigns/{id}                    → CampaignApiController@update
DELETE /api/campaigns/{id}                    → CampaignApiController@destroy
POST   /api/campaigns/{id}/prepare            → CampaignApiController@prepare
GET    /api/campaigns/{id}/recipients          → CampaignApiController@recipients
GET    /api/campaigns/{id}/estimate-cost       → CampaignApiController@estimateCost
POST   /api/campaigns/{id}/send               → CampaignApiController@send
POST   /api/campaigns/{id}/schedule           → CampaignApiController@schedule
POST   /api/campaigns/{id}/pause              → CampaignApiController@pause
POST   /api/campaigns/{id}/resume             → CampaignApiController@resume
POST   /api/campaigns/{id}/cancel             → CampaignApiController@cancel
POST   /api/campaigns/{id}/clone              → CampaignApiController@clone
GET    /api/campaigns/opt-out/numbers          → CampaignApiController@optOutNumbers
POST   /api/campaigns/opt-out/validate-keyword → CampaignApiController@validateOptOutKeyword
```

**Message Template APIs:**
```
GET    /api/message-templates                  → MessageTemplateApiController@index
POST   /api/message-templates                  → MessageTemplateApiController@store
GET    /api/message-templates/{id}             → MessageTemplateApiController@show
PUT    /api/message-templates/{id}             → MessageTemplateApiController@update
DELETE /api/message-templates/{id}             → MessageTemplateApiController@destroy
POST   /api/message-templates/{id}/favourite   → MessageTemplateApiController@toggleFavourite
POST   /api/message-templates/{id}/analyse     → MessageTemplateApiController@analyseContent
```

**Numbers Module APIs:**
```
GET    /api/numbers/library                    → NumberApiController@library
GET    /api/numbers/pool                       → NumberApiController@pool
GET    /api/numbers/pool/pricing               → NumberApiController@poolPricing
POST   /api/numbers/purchase-vmn               → NumberApiController@purchaseVmn
POST   /api/numbers/purchase-keyword           → NumberApiController@purchaseKeyword
POST   /api/numbers/{id}/release               → NumberApiController@release
POST   /api/numbers/{id}/suspend               → NumberApiController@suspend
POST   /api/numbers/{id}/reactivate            → NumberApiController@reactivate
PUT    /api/numbers/{id}/configure             → NumberApiController@configure
GET    /api/numbers/{id}/assignments            → NumberApiController@assignments
POST   /api/numbers/{id}/assignments            → NumberApiController@addAssignment
DELETE /api/numbers/{id}/assignments/{assignmentId} → NumberApiController@removeAssignment
POST   /api/numbers/bulk-release               → NumberApiController@bulkRelease
GET    /api/numbers/{id}/auto-reply-rules       → NumberApiController@autoReplyRules
POST   /api/numbers/{id}/auto-reply-rules       → NumberApiController@storeAutoReplyRule
PUT    /api/numbers/auto-reply-rules/{ruleId}   → NumberApiController@updateAutoReplyRule
DELETE /api/numbers/auto-reply-rules/{ruleId}   → NumberApiController@deleteAutoReplyRule
```

**RCS Agent APIs (customer):**
```
GET    /api/rcs-agents                         → RcsAgentController@apiIndex
POST   /api/rcs-agents                         → RcsAgentController@apiStore
GET    /api/rcs-agents/{uuid}                  → RcsAgentController@apiShow
PUT    /api/rcs-agents/{uuid}                  → RcsAgentController@apiUpdate
DELETE /api/rcs-agents/{uuid}                  → RcsAgentController@apiDestroy
POST   /api/rcs-agents/{uuid}/submit           → RcsAgentController@apiSubmit
POST   /api/rcs-agents/{uuid}/resubmit         → RcsAgentController@apiResubmit
POST   /api/rcs-agents/{uuid}/provide-info     → RcsAgentController@apiProvideInfo
```

**RCS Asset APIs:**
```
POST   /api/rcs-assets/upload                  → RcsAssetController@upload
POST   /api/rcs-assets/{id}/edit               → RcsAssetController@applyEdit
POST   /api/rcs-assets/{id}/finalize           → RcsAssetController@finalize
DELETE /api/rcs-assets/{id}                    → RcsAssetController@destroy
```

**API Connection APIs:**
```
GET    /api/api-connections                     → ApiConnectionController@index
POST   /api/api-connections                     → ApiConnectionController@store
GET    /api/api-connections/{uuid}              → ApiConnectionController@show
PUT    /api/api-connections/{uuid}              → ApiConnectionController@update
POST   /api/api-connections/{uuid}/suspend      → ApiConnectionController@suspend
POST   /api/api-connections/{uuid}/reactivate   → ApiConnectionController@reactivate
POST   /api/api-connections/{uuid}/archive      → ApiConnectionController@archive
POST   /api/api-connections/{uuid}/regenerate-key → ApiConnectionController@regenerateKey
PUT    /api/api-connections/{uuid}/password     → ApiConnectionController@changePassword
```

**Contact Book, Invoice, Purchase APIs** — already in web.php under customer.auth. Do not move back to api.php.

**Confirm & Send page route:**
```
POST   /messages/confirm-send                  → QuickSMSController@confirmAndSend
```

**Account pricing API:**
```
GET    /api/account/pricing                    → QuickSMSController@accountPricingApi
```

### `routes/api_billing.php` — Loaded by RouteServiceProvider

Customer (v1, auth:sanctum + customer.auth):
```
/api/v1/billing/balance, /transactions, /top-up/*, /pricing, /invoices/*, /balance-alerts/*
```

Admin (v1, auth:sanctum + admin.auth):
```
/api/v1/admin/billing/accounts/*, pricing/*, invoices/*, credit-notes/*, recurring/*, reconciliation/*, margin/*
```

Webhooks (no auth, throttle:300,1):
```
POST /webhooks/stripe, /webhooks/xero, /webhooks/hubspot
```

### `routes/api.php` — What was removed (moved to web.php)

The following routes were **removed from api.php** and **moved to web.php** because the `api` middleware group does not carry session cookies, breaking tenant-scoped queries:
- Contact Book APIs (`/api/contacts/*`, `/api/tags/*`, `/api/contact-lists/*`, `/api/opt-out-lists/*`)
- Invoice APIs (`/api/invoices/*`)
- Purchase APIs (`/api/purchase/*`)

**Do NOT move them back to api.php. They must stay in web.php under the customer.auth middleware.**

---

## Step 4: What NOT to Do

This is the most important section. **Replit agent: obey these constraints absolutely.**

1. **Do NOT create any new migrations.** All schema changes are in the pulled migrations. Run `php artisan migrate --force`.
2. **Do NOT modify any pulled migration files.** They use PostgreSQL-specific syntax (enums, triggers, RLS). Do not convert to MySQL.
3. **Do NOT rename or refactor any service classes.** The class names, method signatures, and namespaces are exact.
4. **Do NOT move routes between files.** Routes in `web.php` must stay in `web.php`. Routes in `api_billing.php` must stay there.
5. **Do NOT rewrite controllers.** The controller methods, their validation rules, and their response formats are final.
6. **Do NOT add mock data.** All data comes from the database.
7. **Do NOT change the model pattern.** All models use UUID PKs, global tenant scope, and `toPortalArray()`.
8. **Do NOT change the middleware.** Customer APIs use `customer.auth` + `throttle:60,1`. Admin APIs use `admin.auth`. Webhooks use `throttle:300,1`.
9. **Do NOT introduce new packages or dependencies.** Everything uses existing Laravel + PostgreSQL features.
10. **Do NOT modify `setup.sh`.** It is configured for Replit's PostgreSQL environment (helium host, heliumdb database).
11. **Do NOT touch billing services** (`BalanceService`, `PricingEngine`, `LedgerService`, `InvoiceService`). They are called by the new services but must not be modified.
12. **Do NOT modify the `config/billing.php` values** unless explicitly asked. The VAT rate, currency, and limits are set.
13. **Do NOT create SQLite migrations or use MySQL syntax.** The database is PostgreSQL 16.

---

## Step 5: Post-Merge Verification Checklist

Run each of these after merging. All must pass.

```bash
# 1. Migrations run without error
php artisan migrate --force

# 2. Routes are registered (spot-check)
php artisan route:list --path=api/campaigns | head -20
php artisan route:list --path=api/numbers | head -20
php artisan route:list --path=api/message-templates | head -10
php artisan route:list --path=api/rcs-agents | head -10

# 3. Models can be instantiated
php artisan tinker --execute="new App\Models\Campaign(); echo 'OK';"
php artisan tinker --execute="new App\Models\CampaignRecipient(); echo 'OK';"
php artisan tinker --execute="new App\Models\MessageTemplate(); echo 'OK';"
php artisan tinker --execute="new App\Models\PurchasedNumber(); echo 'OK';"
php artisan tinker --execute="new App\Models\Billing\CampaignEstimateSnapshot(); echo 'OK';"

# 4. Config loads
php artisan tinker --execute="echo config('billing.vat_rate');"

# 5. No syntax errors
php artisan route:clear && php artisan config:clear && php artisan view:clear

# 6. Server starts
php artisan serve --host=0.0.0.0 --port=5000
```

---

## Step 6: What Replit Can Safely Work On AFTER Merging

After the merge is complete and verified, Replit is free to work on **UI/frontend only** for these modules:

- **Send Message page** (`send-message.blade.php`): Wire the form UI to the campaign APIs listed above. The backend is ready.
- **Numbers management page**: Build a Blade page that calls the `/api/numbers/*` endpoints.
- **RCS content creator** in the send-message wizard: Build the card/carousel editor UI that calls `/api/rcs-assets/*` endpoints.
- **Campaign history/reporting page**: Build a Blade page that lists campaigns via `GET /api/campaigns`.

**Do NOT create new backend routes, controllers, services, or migrations for these. The backend already exists. Just build the Blade + jQuery + Bootstrap 5 frontend that calls the existing API endpoints.**

---

## File Count Summary

| Category | Count |
|---|---|
| New migrations | 9 |
| New models | 12 |
| New services | 11 |
| New jobs | 3 |
| New controllers | 4 |
| Modified controllers | 2 |
| New route file | 1 (api_billing.php) |
| Modified route files | 2 (web.php, api.php) |
| New config | 1 (billing.php) |
| New provider | 1 (BillingServiceProvider) |
| New frontend | 4 (security-helpers.js, 3 opt-out views) |
| Modified blade | 1 (confirm-campaign) |
| Setup script | 1 (setup.sh) |
| **Total new/modified files** | **52** |

# Replit: Pull & Merge — Message Templates Module (Customer + Admin Portals)

## READ THIS ENTIRE FILE BEFORE DOING ANYTHING

This prompt delivers **one build only**: the **Message Templates Module** across BOTH customer and admin portals. It tells you exactly which files to pull, what they do, and what to leave alone. **Do not touch any other module. Do not refactor. Do not "improve" unrelated files.**

The Message Templates Module is the screen where customers create/edit/manage reusable message templates (at `/management/templates`) and where admins view/edit templates across all accounts in the Global Templates Library (at `/admin/management/templates`).

---

## ANTI-DRIFT RULES — READ FIRST

These rules override any default Replit agent behaviour. Obey them absolutely.

1. **ONLY touch files listed in this document.** If a file is not listed below, do not open, edit, rename, move, or delete it.
2. **Do NOT modify any other module.** The following modules are FROZEN — no changes whatsoever:
   - Contact Book (`contacts/`, `ContactBookApiController`, `Contact.php`, `ContactList.php`, `OptOutList.php`, `OptOutRecord.php`, `Tag.php`)
   - Billing & Invoicing (`Billing/`, `api_billing.php`, `InvoiceApiController`, billing blade views)
   - Numbers Management (`numbers.blade.php`, `numbers-configure.blade.php`, `NumberApiController`, `NumberService`, `NumberBillingService`, `PurchasedNumber`, `VmnPoolNumber`, `ShortcodeKeyword`)
   - RCS Agent Registration (`rcs-agent-wizard.blade.php`, `rcs-agent.blade.php`, `RcsAgentController`, `RcsAgent.php`, RCS admin views)
   - API Connections (`api-connections.blade.php`, `api-connection-wizard.blade.php`, `ApiConnectionController`)
   - Spam Filter & Security (`ContentRule`, `UrlRule`, `SenderidRule`, `EnforcementExemption`)
   - Admin Login & User Management (`AdminController`, admin login migrations)
   - Sender ID Management (`SenderIdController`, `SenderId.php`, sender ID blade views)
   - Send Message page (`send-message.blade.php`) — do NOT edit
   - Confirm Campaign page (`confirm-campaign.blade.php`) — do NOT edit
   - Campaign History page (`campaign-history.blade.php`) — do NOT edit
   - Dashboard (`dashboard.blade.php`) — do NOT edit
   - Reporting Dashboard (`reporting/dashboard.blade.php`) — do NOT edit
   - Purchase pages (`purchase/messages.blade.php`, `purchase/numbers.blade.php`)
   - Account Details (`account/details.blade.php`, `account/activate.blade.php`)
   - Opt-Out Landing pages (`optout/landing.blade.php`, `optout/confirmed.blade.php`, `optout/invalid.blade.php`)
   - Sidebar navigation (`elements/quicksms-sidebar.blade.php`) — do NOT edit
   - Layout (`layouts/quicksms.blade.php`) — do NOT edit
   - All database migrations — do NOT create new ones, do NOT modify existing ones
   - All config files (`config/billing.php`, `config/app.php`, `config/services.php`)
   - `setup.sh`, `.replit`, `replit.nix`
   - All `REPLIT_PROMPT_*.md` and `REPLIT_PULL_AND_MERGE*.md` files
3. **Do NOT create new migrations.** The `message_templates` table already exists with all necessary columns.
4. **Do NOT create new models.** `MessageTemplate.php` already exists and is complete.
5. **Do NOT create new services or controllers.** `MessageTemplateApiController.php` already exists with full CRUD + analysis.
6. **Do NOT add packages or dependencies.**
7. **Do NOT convert PostgreSQL syntax to MySQL.** The database is PostgreSQL 16.
8. **Do NOT modify `routes/web.php`** — all template routes already exist (see Section 2H).
9. **Do NOT modify `routes/api.php` or `routes/api_billing.php`.**
10. **Do NOT modify Campaign models, services, or controllers.** Campaigns use templates but are a separate frozen module.

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

---

## Step 2: Files Delivered in This Build

### THE ONLY FILES YOU SHOULD CARE ABOUT:

---

#### 2A. Customer Portal — Templates Library Page

| File | Status |
|---|---|
| `resources/views/quicksms/management/templates.blade.php` | **MODIFIED** — updated with new feature fields |

This blade file is the customer-facing template management page at `/management/templates`. It contains:

- **Template list table** with columns: Name, Channel, Type, Sender ID, Status, Created, Actions
- **Create/Edit wizard** (multi-step inline: Metadata → Content → Opt-out/Features → Review)
- **Opt-out configuration UI**: enable toggle, method dropdown (reply/url/both), keyword input, opt-out text, opt-out number selection
- **Sender ID & RCS Agent dropdowns**: bound to the customer's purchased sender IDs and registered RCS agents
- **Trackable link configuration**: enable toggle, domain input (default `qsms.uk`)
- **Message expiry configuration**: enable toggle, duration dropdown (1h, 4h, 12h, 24h, 48h, 72h, 7d)
- **Content analysis**: live character count, segment count, encoding detection, placeholder extraction
- **Favourites toggle** and **status management** (draft/active/archive)

**Design language**: Purple brand `#886CC0`, Bootstrap 5, vanilla JavaScript, no Vue/React/Livewire.

---

#### 2B. Admin Portal — Global Templates Library Page

| File | Status |
|---|---|
| `resources/views/admin/management/templates.blade.php` | **MODIFIED** — updated with new feature columns and detail modal fields |

This blade file is the admin-facing Global Templates Library at `/admin/management/templates`. It contains:

- **Cross-account template table** with columns: Account, Name, ID, Ver, Channel, **Sender / Agent**, Trigger, Preview, **Features**, Scope, Status, Updated
- **Sender / Agent column**: Shows sender ID (with `fa-id-badge` icon) and RCS agent (with `fa-robot` icon)
- **Features column**: Badge pills for opt-out (green), trackable link (info/cyan), message expiry (warning/yellow)
- **Detail modal** with all fields: Sender ID, RCS Agent, Opt-out (method/keyword/text), Trackable Link (domain), Message Expiry (duration)
- **Filters**: Account, Channel, Status, Access Scope, search bar
- **Global admin actions**: View details, Edit (redirects to edit wizard), Change Status

**Design language**: Navy brand `#1e3a5f`, Bootstrap 5, vanilla JavaScript.

Helper functions added:
- `getSenderAgentLabel(template)` — renders sender ID + RCS agent with icons
- `getFeaturesIcons(template)` — renders feature badge pills

---

#### 2C. Admin Portal — Template Edit Wizard

| File | Status |
|---|---|
| `resources/views/admin/management/template-edit-wizard.blade.php` | **MODIFIED** — added new feature controls to edit steps |

This blade file is the admin template edit wizard at `/admin/management/templates/{accountId}/{templateId}/edit`. It contains:

- **Step 1 (Metadata)**: Name, description, channel, category, status, access scope + **NEW**: Sender ID input, RCS Agent input, Opt-out Configuration (enable toggle, method, keyword, text), Trackable Link (enable toggle, domain), Message Expiry (enable toggle, duration)
- **Step 2 (Content)**: Template body editor, RCS content editor, placeholder detection
- **Step 3 (Review)**: Read-only summary of all fields including **NEW**: Sender ID, RCS Agent, Features card (opt-out, trackable link, expiry)

Toggle functions:
- `toggleOptOutSection()` — shows/hides opt-out fields when toggle is flipped
- `toggleTrackableLinkSection()` — shows/hides trackable link domain field
- `toggleMessageExpirySection()` — shows/hides expiry duration field

`loadTemplate()` populates all new fields from template data.
`populateReview()` renders new fields in the review step.
`saveTemplate()` includes all new fields in the PUT payload.

---

#### 2D. Admin Templates Service (Mock Data)

| File | Status |
|---|---|
| `public/js/admin-templates-service.js` | **MODIFIED** — mock data updated with new fields |

This JavaScript service provides mock template data for the admin portal. All 12 mock templates now include:

| Field | Type | Example Values |
|---|---|---|
| `senderId` | string/null | `'QuickSMS'`, `'HealthCare'`, `null` |
| `rcsAgent` | string/null | `'Acme Brand Agent'`, `null` |
| `optOutEnabled` | boolean | `true`, `false` |
| `optOutMethod` | string/null | `'reply'`, `'url'`, `'both'` |
| `optOutKeyword` | string/null | `'STOP'`, `'OPTOUT'`, `'END'` |
| `optOutText` | string/null | `'Reply STOP to opt out'` |
| `trackableLinkEnabled` | boolean | `true`, `false` |
| `trackableLinkDomain` | string/null | `'qsms.uk'`, `'track.acme.co'` |
| `messageExpiryEnabled` | boolean | `true`, `false` |
| `messageExpiryValue` | string/null | `'24h'`, `'48h'`, `'7d'` |

The service has `useMockData: true` — when wired to real backend, set to `false` and API calls go to the actual endpoints.

---

#### 2E. Customer Templates Service (API Wrapper)

| File | Status |
|---|---|
| `public/js/template-service.js` | **EXISTING — DO NOT MODIFY** |

This is a thin fetch wrapper that forwards whatever data object the caller provides directly to the API. It does NOT enumerate field names — the customer blade file constructs the data object inline. Endpoints:

| Method | HTTP | Endpoint |
|---|---|---|
| `list(params)` | GET | `/api/message-templates` |
| `create(data)` | POST | `/api/message-templates` |
| `get(id)` | GET | `/api/message-templates/{id}` |
| `update(id, data)` | PUT | `/api/message-templates/{id}` |
| `delete(id)` | DELETE | `/api/message-templates/{id}` |
| `toggleFavourite(id)` | POST | `/api/message-templates/{id}/toggle-favourite` |
| `analyseContent(content)` | POST | `/api/message-templates/analyse-content` |

---

#### 2F. Backend Model (already complete — DO NOT MODIFY)

| File | Status |
|---|---|
| `app/Models/MessageTemplate.php` | **EXISTING** — complete with all fields |

The model has 33 fillable fields. The template feature fields are:

| Field | Type | Validation |
|---|---|---|
| `sender_id_id` | uuid (FK → sender_ids) | nullable, uuid |
| `rcs_agent_id` | uuid (FK → rcs_agents) | nullable, uuid |
| `opt_out_enabled` | boolean | nullable |
| `opt_out_method` | string | nullable, in: reply, url, both |
| `opt_out_number_id` | uuid (FK → purchased_numbers) | nullable, uuid |
| `opt_out_keyword` | string | nullable, min:4, max:10, regex: alphanumeric |
| `opt_out_text` | string | nullable, max:500 |
| `opt_out_list_id` | uuid (FK → opt_out_lists) | nullable, uuid |
| `opt_out_url_enabled` | boolean | nullable |
| `opt_out_screening_list_ids` | array (JSON cast) | nullable, array of uuids |
| `trackable_link_enabled` | boolean | nullable |
| `trackable_link_domain` | string | nullable, max:255 |
| `message_expiry_enabled` | boolean | nullable |
| `message_expiry_value` | string | nullable, max:10 |

Relationships: `senderId()`, `rcsAgent()`, `optOutNumber()`, `optOutList()`, `campaigns()`

`toPortalArray()` serialises all fields including a derived `opt_out_number` (the actual number string via `$this->optOutNumber?->number`).

UUID primary key. Global tenant scope on `account_id`. SoftDeletes.

---

#### 2G. Backend API Controller (already complete — DO NOT MODIFY)

| File | Status |
|---|---|
| `app/Http/Controllers/Api/MessageTemplateApiController.php` | **EXISTING** — full CRUD |

Methods:
- `index()` — paginated list with search, status filter, category filter, tenant scope
- `store()` — validates all 22+ fields, creates template, recalculates metadata
- `show()` — returns single template via `toPortalArray()`
- `update()` — validates all fields (with `sometimes` rules), updates, recalculates metadata if content changed
- `destroy()` — soft deletes
- `toggleFavourite()` — flips `is_favourite`
- `analyseContent()` — stateless content analysis (encoding, chars, segments, placeholders)

---

#### 2H. Routes (already exist — DO NOT MODIFY `routes/web.php`)

**Customer portal routes:**
```
GET  /management/templates                          → QuickSMSController@templates
GET  /management/templates/create                   → QuickSMSController@templateCreateStep1
GET  /management/templates/create/step1             → QuickSMSController@templateCreateStep1
GET  /management/templates/create/step2             → QuickSMSController@templateCreateStep2
GET  /management/templates/create/step3             → QuickSMSController@templateCreateStep3
GET  /management/templates/create/review            → QuickSMSController@templateCreateReview
GET  /management/templates/{templateId}/edit        → QuickSMSController@templateEditStep1
GET  /management/templates/{templateId}/edit/step1  → QuickSMSController@templateEditStep1
GET  /management/templates/{templateId}/edit/step2  → QuickSMSController@templateEditStep2
GET  /management/templates/{templateId}/edit/step3  → QuickSMSController@templateEditStep3
GET  /management/templates/{templateId}/edit/review → QuickSMSController@templateEditReview
```

**Customer template API routes:**
```
GET    /api/message-templates                       → MessageTemplateApiController@index
POST   /api/message-templates                       → MessageTemplateApiController@store
GET    /api/message-templates/{id}                  → MessageTemplateApiController@show
PUT    /api/message-templates/{id}                  → MessageTemplateApiController@update
DELETE /api/message-templates/{id}                  → MessageTemplateApiController@destroy
POST   /api/message-templates/{id}/toggle-favourite → MessageTemplateApiController@toggleFavourite
POST   /api/message-templates/analyse-content       → MessageTemplateApiController@analyseContent
```

**Admin portal routes:**
```
GET  /admin/assets/templates                                              → AdminController@assetsTemplates
GET  /admin/management/templates                                          → AdminController@managementTemplates
GET  /admin/management/templates/{accountId}/{templateId}/edit            → redirect to step1
GET  /admin/management/templates/{accountId}/{templateId}/edit/step1      → AdminController@adminTemplateEditStep1
GET  /admin/management/templates/{accountId}/{templateId}/edit/step2      → AdminController@adminTemplateEditStep2
GET  /admin/management/templates/{accountId}/{templateId}/edit/step3      → AdminController@adminTemplateEditStep3
GET  /admin/management/templates/{accountId}/{templateId}/edit/review     → AdminController@adminTemplateEditReview
```

---

## Field Parity Table — Customer Portal vs Admin Portal vs Backend Model

This table ensures no field is missed across the three layers. Every field listed here must appear in ALL three columns.

| Feature | Customer Blade Field ID / Key | Admin Blade Field ID / Key | Backend Model Column |
|---|---|---|---|
| Sender ID | Dropdown bound to customer sender IDs | `#templateSenderId` text input | `sender_id_id` (uuid FK) |
| RCS Agent | Dropdown bound to customer RCS agents | `#templateRcsAgent` text input | `rcs_agent_id` (uuid FK) |
| Opt-out Enabled | Toggle switch | `#optOutEnabled` checkbox | `opt_out_enabled` (boolean) |
| Opt-out Method | Dropdown: reply/url/both | `#optOutMethod` select | `opt_out_method` (string) |
| Opt-out Number | Dropdown bound to purchased numbers | *(not in admin edit — admin views only)* | `opt_out_number_id` (uuid FK) |
| Opt-out Keyword | Text input (max 10 chars) | `#optOutKeyword` text input | `opt_out_keyword` (string) |
| Opt-out Text | Text input | `#optOutText` text input | `opt_out_text` (string) |
| Opt-out List | Dropdown bound to opt-out lists | *(not in admin edit — admin views only)* | `opt_out_list_id` (uuid FK) |
| Opt-out URL | Toggle switch | *(not in admin edit — admin views only)* | `opt_out_url_enabled` (boolean) |
| Opt-out Screening Lists | Multi-select | *(not in admin edit — admin views only)* | `opt_out_screening_list_ids` (array) |
| Trackable Link Enabled | Toggle switch | `#trackableLinkEnabled` checkbox | `trackable_link_enabled` (boolean) |
| Trackable Link Domain | Text input | `#trackableLinkDomain` text input | `trackable_link_domain` (string) |
| Message Expiry Enabled | Toggle switch | `#messageExpiryEnabled` checkbox | `message_expiry_enabled` (boolean) |
| Message Expiry Value | Duration dropdown | `#messageExpiryValue` select | `message_expiry_value` (string) |

**Note on admin simplification**: The admin edit wizard uses text inputs for Sender ID and RCS Agent (not bound dropdowns) because the admin views all accounts globally. The customer portal uses account-bound dropdowns. The admin detail modal and table show these fields read-only with icon labels. Some opt-out sub-fields (number, list, URL, screening lists) are read-only in admin context — the admin can view them in the detail modal but not edit them in the wizard.

---

## Step 3: What Replit Can Do AFTER Merging

After the merge is verified (Step 5 below), Replit may **ONLY** work on these tasks:

### Task 1: Wire Admin Templates Service to Real API

Replace mock data in `public/js/admin-templates-service.js`:
- Set `useMockData: false`
- Ensure API calls use the correct admin API endpoints (these may need admin-specific routes — check if they exist)
- The admin service currently uses mock data with `useMockData: true`. The real API should query `MessageTemplate` across all accounts (admin-scoped, no tenant filter)

### Task 2: Wire Customer Template Create/Edit to Real API

If the customer blade template create/edit wizard is still using mock data or incomplete API calls, wire the form submissions to:
```
POST /api/message-templates          (create)
PUT  /api/message-templates/{id}     (update)
```

Ensure ALL fields from the Field Parity Table (Section above) are included in the request payload.

### Task 3 (Optional): Add Admin-Specific API Endpoints

If admin template CRUD doesn't have dedicated routes, create admin-scoped versions under `/admin/api/templates` that bypass tenant scope. These would need:
- `admin.auth` middleware (not `customer.auth`)
- No `account_id` session filter — query all accounts
- Read-only for most actions; edit should require admin role

### FORBIDDEN After Merge:
- Do NOT add new database migrations
- Do NOT modify `MessageTemplate.php` model
- Do NOT modify `MessageTemplateApiController.php`
- Do NOT modify `routes/web.php`
- Do NOT modify any file not listed in Section 2
- Do NOT rename any methods or classes
- Do NOT change the customer or admin blade design language (purple brand / navy brand)
- Do NOT convert PostgreSQL syntax to MySQL

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
| Admin Login & Users | `AdminController.php`, admin login migrations | FROZEN |
| Send Message | `send-message.blade.php`, `rcs-wizard.js`, `rcs-preview-renderer.js` | FROZEN |
| Confirm Campaign | `confirm-campaign.blade.php` | FROZEN |
| Campaign History | `campaign-history.blade.php`, `CampaignApiController.php` | FROZEN |
| Dashboard | `dashboard.blade.php` | FROZEN |
| Reporting | `reporting/dashboard.blade.php`, `message-log.blade.php`, `finance-data.blade.php` | FROZEN |
| Purchase Pages | `purchase/messages.blade.php`, `purchase/numbers.blade.php` | FROZEN |
| Account Pages | `account/details.blade.php`, `account/activate.blade.php`, `account/security.blade.php` | FROZEN |
| Opt-Out Landing | `optout/*.blade.php`, `OptOutLandingController.php` | FROZEN |
| Layout & Navigation | `layouts/quicksms.blade.php`, `elements/quicksms-sidebar.blade.php`, `elements/admin-sidebar.blade.php` | FROZEN |
| All Migrations | Every file in `database/migrations/` | FROZEN |
| All Config | `config/billing.php`, `config/app.php`, `config/services.php` | FROZEN |
| Routes | `routes/web.php`, `routes/api.php`, `routes/api_billing.php` | FROZEN |
| Setup | `setup.sh`, `.replit`, `replit.nix` | FROZEN |
| Campaign Models | `Campaign.php`, `CampaignRecipient.php`, `CampaignOptOutUrl.php`, `CampaignEstimateSnapshot.php` | FROZEN |
| Campaign Services | `CampaignService.php`, `BillingPreflightService.php`, `DeliveryService.php`, `RecipientResolverService.php` | FROZEN |
| Billing Services | `BalanceService.php`, `PricingEngine.php`, `LedgerService.php`, `InvoiceService.php` | FROZEN |

---

## Step 5: Post-Merge Verification Checklist

Run each command. All must pass. If any fails, **stop and investigate — do not attempt to fix by editing frozen files.**

```bash
# 1. Clear all caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# 2. Run migrations (should say "Nothing to migrate" if already up to date)
php artisan migrate --force

# 3. Verify customer template routes exist
php artisan route:list --path=management/templates | head -20

# 4. Verify template API routes exist
php artisan route:list --path=api/message-templates | head -10

# 5. Verify admin template routes exist
php artisan route:list --path=admin/management/templates | head -10

# 6. Verify MessageTemplate model loads
php artisan tinker --execute="new App\Models\MessageTemplate(); echo 'MessageTemplate OK';"

# 7. Verify NO syntax errors across the app
php artisan route:clear && php artisan config:clear

# 8. Start server and verify pages load
php artisan serve --host=0.0.0.0 --port=5000

# Visit these URLs (after login as CUSTOMER):
# /management/templates                              → Customer template library
# /management/templates/create                       → Customer template create wizard

# Visit these URLs (after login as ADMIN):
# /admin/management/templates                        → Admin Global Templates Library
# /admin/management/templates/{accountId}/{templateId}/edit  → Admin template edit wizard

# 9. REGRESSION CHECK — verify these OTHER pages still load without errors:
# Visit: /dashboard
# Visit: /contacts/all
# Visit: /contacts/lists
# Visit: /contacts/opt-out-lists
# Visit: /management/numbers
# Visit: /management/rcs-agent
# Visit: /management/api-connections
# Visit: /messages/send-message
# Visit: /messages/campaign-history
# Visit: /purchase/numbers
# Visit: /account/details
# Visit: /reporting/invoices
# Visit: /admin/dashboard
```

---

## Step 6: Summary of This Build

| Item | Detail |
|---|---|
| **Module** | Message Templates (Customer + Admin) |
| **Customer URL** | `/management/templates` |
| **Admin URL** | `/admin/management/templates` |
| **Customer route name** | `management.templates` |
| **Admin route name** | `admin.management.templates` |
| **Customer view file** | `resources/views/quicksms/management/templates.blade.php` |
| **Admin list view file** | `resources/views/admin/management/templates.blade.php` |
| **Admin edit view file** | `resources/views/admin/management/template-edit-wizard.blade.php` |
| **Admin JS service** | `public/js/admin-templates-service.js` |
| **Customer JS service** | `public/js/template-service.js` (thin wrapper — DO NOT MODIFY) |
| **Backend model** | `app/Models/MessageTemplate.php` (complete — DO NOT MODIFY) |
| **Backend API controller** | `app/Http/Controllers/Api/MessageTemplateApiController.php` (complete — DO NOT MODIFY) |
| **New features added** | Sender ID, RCS Agent, Opt-out (method/keyword/text), Trackable Link (domain), Message Expiry (duration) |
| **Current data source** | Customer: API-backed. Admin: Mock data (`useMockData: true`) |
| **Next step for Replit** | Wire admin mock data to real admin API endpoints |
| **Files touched** | 4 files (2 admin blade views, 1 admin JS service, 1 customer blade view) |
| **Files frozen** | Everything else (469+ files) |
| **New dependencies** | None |
| **New migrations** | None (message_templates table already has all columns) |
| **Risk to other modules** | Zero — this build modifies UI for template pages only |

---

## FINAL WARNING TO REPLIT AGENT

**Your job is to merge and verify. Then optionally wire admin mock data to real admin API. That's it.**

- If you feel the urge to "clean up" another file → **STOP**
- If you feel the urge to add a migration → **STOP**
- If you feel the urge to refactor the MessageTemplate model → **STOP**
- If you feel the urge to modify routes → **STOP**
- If you feel the urge to improve a controller you weren't asked to touch → **STOP**
- If you feel the urge to change the customer template blade beyond what was delivered → **STOP**
- If you feel the urge to modify the campaign module → **STOP**
- If a page other than templates has an issue → **do NOT fix it in this merge**

**One build. Two portals. Zero drift.**

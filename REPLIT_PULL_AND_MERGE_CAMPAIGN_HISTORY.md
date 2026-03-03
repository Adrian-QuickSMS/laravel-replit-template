# Replit: Pull & Merge — Campaign History Module ONLY

## READ THIS ENTIRE FILE BEFORE DOING ANYTHING

This prompt delivers **one build only**: the **Campaign History Module**. It tells you exactly which files to pull, what they do, and what to leave alone. **Do not touch any other module. Do not refactor. Do not "improve" unrelated files.**

The Campaign History Module is the screen at `/messages/campaign-history` where customers view their past, active, and scheduled campaigns in a searchable, filterable table.

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
   - Send Message page (`send-message.blade.php`) — may link TO campaign history but do NOT edit
   - Confirm Campaign page (`confirm-campaign.blade.php`) — may link TO campaign history but do NOT edit
   - Dashboard (`dashboard.blade.php`) — may link TO campaign history but do NOT edit
   - Reporting Dashboard (`reporting/dashboard.blade.php`) — may link TO campaign history but do NOT edit
   - Purchase pages (`purchase/messages.blade.php`, `purchase/numbers.blade.php`)
   - Account Details (`account/details.blade.php`, `account/activate.blade.php`)
   - Opt-Out Landing pages (`optout/landing.blade.php`, `optout/confirmed.blade.php`, `optout/invalid.blade.php`)
   - Sidebar navigation (`elements/quicksms-sidebar.blade.php`) — already has the campaign history link, do NOT edit
   - Layout (`layouts/quicksms.blade.php`) — do NOT edit
   - All database migrations — do NOT create new ones, do NOT modify existing ones
   - All config files (`config/billing.php`, `config/app.php`, `config/services.php`)
   - `setup.sh`, `.replit`, `replit.nix`
   - All `REPLIT_PROMPT_*.md` and `REPLIT_PULL_AND_MERGE.md` files
3. **Do NOT create new migrations.** The `campaigns` table and all related tables already exist.
4. **Do NOT create new models.** `Campaign.php`, `CampaignRecipient.php`, `CampaignOptOutUrl.php`, and `CampaignEstimateSnapshot.php` already exist and are complete.
5. **Do NOT create new services.** `CampaignService.php`, `BillingPreflightService.php`, `DeliveryService.php`, `RecipientResolverService.php` already exist.
6. **Do NOT create new API controllers.** `CampaignApiController.php` already exists with a full `index()` method.
7. **Do NOT add packages or dependencies.**
8. **Do NOT convert PostgreSQL syntax to MySQL.** The database is PostgreSQL 16.
9. **Do NOT modify `routes/web.php`** — the route already exists:
   ```
   Route::get('/messages/campaign-history', 'campaignHistory')->name('messages.campaign-history');
   ```
10. **Do NOT modify `routes/api.php` or `routes/api_billing.php`** — campaign API routes already exist.

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

#### 2A. Primary View — Campaign History Page

| File | Lines | Status |
|---|---|---|
| `resources/views/quicksms/messages/campaign-history.blade.php` | 2,126 | **MODIFIED** — already exists on main, updated version from Claude branch |

This blade file contains:
- **RBAC permission stubs** (lines 18-43): Viewer/Editor/Admin role checks — currently hardcoded to `admin`, ready for Spatie integration
- **GDPR compliance notes** (lines 45-52): PII handling rules
- **Full CSS** for campaign history table (responsive, cards, filters, status badges)
- **Campaign table** with columns: Name, Channel, Status, Recipients (delivered/total), Send Date, Sender ID, RCS Agent, Tags, Template, Tracking, Opt-Out
- **Status filter pills**: All, Pending, Scheduled, Sending, Complete, Cancelled, Failed
- **Search bar**: Filters by campaign name, sender ID, RCS agent
- **Action dropdown menu** per campaign: View Details, Edit Draft, Duplicate, Cancel, Export Delivery Report
- **Pagination** placeholder (currently client-side, TODO: server-side via API)

**Current state**: The view is fully styled and functional as a UI. It receives `$campaigns` from the controller. Currently the controller passes **mock data** (16 hardcoded campaigns). The view itself is production-ready — only the data source needs wiring.

#### 2B. Controller Method — `campaignHistory()`

| File | Method | Lines |
|---|---|---|
| `app/Http/Controllers/QuickSMSController.php` | `campaignHistory()` | 1692–1718 |

**Current state**: Returns mock data array with 16 campaigns. This is the method to wire to the real backend.

**What the method should eventually do** (Replit's task AFTER merging):
```php
public function campaignHistory(Request $request)
{
    $accountId = session('account_id');
    $campaigns = \App\Models\Campaign::where('account_id', $accountId)
        ->orderBy('created_at', 'desc')
        ->paginate(25);

    return view('quicksms.messages.campaign-history', [
        'page_title' => 'Campaign History',
        'campaigns' => $campaigns,
    ]);
}
```

Or use the existing API endpoint:
```
GET /api/campaigns?page=1&limit=25&status=all
```
which is already handled by `CampaignApiController@index`.

#### 2C. Campaign Model (already complete — DO NOT MODIFY)

| File | Lines | Status |
|---|---|---|
| `app/Models/Campaign.php` | 641 | **NEW** — does not exist on main, delivered by Claude branch |

Key features already built:
- Status constants: `STATUS_DRAFT`, `STATUS_SCHEDULED`, `STATUS_QUEUED`, `STATUS_SENDING`, `STATUS_PAUSED`, `STATUS_COMPLETED`, `STATUS_CANCELLED`, `STATUS_FAILED`
- State machine with `transitionTo()` and `canTransitionTo()`
- Status check methods: `isDraft()`, `isScheduled()`, `isSending()`, etc.
- Query scopes: `scopeOfStatus()`, `scopeDrafts()`, `scopeScheduled()`, `scopeActive()`, `scopeCompleted()`
- Relationships: `campaignRecipients`, `campaignOptOutUrls`, `campaignReservations`, `messageLog`, `estimateSnapshot`
- `toPortalArray()` method for JSON serialisation
- UUID primary key, global tenant scope

#### 2D. Campaign Recipients Model (already complete — DO NOT MODIFY)

| File | Lines | Status |
|---|---|---|
| `app/Models/CampaignRecipient.php` | ~424 | **NEW** — does not exist on main |

Tracks per-recipient delivery status. Provides aggregation scopes the view will need.

#### 2E. Campaign API Controller (already complete — DO NOT MODIFY)

| File | Status |
|---|---|
| `app/Http/Controllers/Api/CampaignApiController.php` | **NEW** — full CRUD + lifecycle actions |

The `index()` method already supports:
- Pagination (`?page=X&limit=Y`)
- Status filtering (`?status=draft,completed`)
- Tenant-scoped queries (only shows campaigns for the logged-in account)

#### 2F. Campaign Migrations (already exist — DO NOT MODIFY, DO NOT CREATE NEW)

| Migration | Purpose |
|---|---|
| `2026_02_24_000002_create_campaigns_table.php` | Main campaigns table |
| `2026_02_24_000003_create_campaign_recipients_table.php` | Per-recipient delivery tracking |
| `2026_02_24_100001_add_campaign_preparation_tracking.php` | preparation_status column |
| `2026_02_24_151751_add_delivery_settings_to_campaigns_table.php` | Delivery settings columns |
| `2026_02_25_000002_add_campaign_opt_out_fields.php` | Opt-out columns |
| `2026_02_25_000004_add_opt_out_screening_list_to_campaigns.php` | Screening list column |
| `2026_03_02_000001_create_campaign_estimate_snapshots_table.php` | Cost snapshot table |

All migrations use PostgreSQL syntax (enums, triggers, RLS). **Do NOT convert to MySQL.**

#### 2G. Route (already exists — DO NOT MODIFY `routes/web.php`)

```
Route::get('/messages/campaign-history', 'campaignHistory')->name('messages.campaign-history');
```

This route is inside the `customer.auth` middleware group, under the `QuickSMSController` resource. It is already registered.

API route also already exists:
```
GET /api/campaigns → CampaignApiController@index
```

---

## Step 3: What Replit Can Do AFTER Merging

After the merge is verified (Step 5 below), Replit may **ONLY** work on these tasks:

### Task 1: Wire `campaignHistory()` to Real Data
Replace the mock data array in `QuickSMSController::campaignHistory()` (lines 1695-1712) with a real database query:
```php
$accountId = session('account_id');
$campaigns = \App\Models\Campaign::where('account_id', $accountId)
    ->orderBy('created_at', 'desc')
    ->paginate(25);
```

### Task 2: Update the Blade View to Use Eloquent Properties
The mock data uses array keys like `$campaign['name']`. After wiring to real data, update the blade to use Eloquent model properties: `$campaign->name`, `$campaign->status`, `$campaign->recipients_total`, etc. Or use the `toPortalArray()` method which returns the same structure.

### Task 3 (Optional): Add Server-Side Filtering
The blade already has filter pills and a search bar. Wire them to query parameters:
```
/messages/campaign-history?status=completed&search=promo&page=2
```

### FORBIDDEN After Merge:
- Do NOT add new routes
- Do NOT add new controllers
- Do NOT add new models
- Do NOT add new migrations
- Do NOT modify any file not listed in Section 2
- Do NOT refactor the Campaign model
- Do NOT rename any methods or classes

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
| Campaign Services | `CampaignService.php`, `BillingPreflightService.php`, `DeliveryService.php`, `RecipientResolverService.php` | FROZEN |
| Campaign API Controller | `CampaignApiController.php` | FROZEN |
| Billing Services | `BalanceService.php`, `PricingEngine.php`, `LedgerService.php`, `InvoiceService.php` | FROZEN |

---

## Step 5: Post-Merge Verification Checklist

Run each command. All must pass. If any fails, **stop and investigate — do not attempt to fix by editing frozen files.**

```bash
# 1. Clear all caches
php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear

# 2. Run migrations (should say "Nothing to migrate" if already up to date)
php artisan migrate --force

# 3. Verify campaign history route exists
php artisan route:list --path=messages/campaign-history

# 4. Verify campaign API routes exist
php artisan route:list --path=api/campaigns | head -20

# 5. Verify Campaign model loads
php artisan tinker --execute="new App\Models\Campaign(); echo 'Campaign OK';"
php artisan tinker --execute="new App\Models\CampaignRecipient(); echo 'CampaignRecipient OK';"

# 6. Verify NO syntax errors across the app
php artisan route:clear && php artisan config:clear

# 7. Verify the campaign history page loads (start server, visit URL)
php artisan serve --host=0.0.0.0 --port=5000
# Visit: http://localhost:5000/messages/campaign-history (after login)

# 8. REGRESSION CHECK — verify these OTHER pages still load without errors:
# Visit: /dashboard
# Visit: /contacts/all
# Visit: /contacts/lists
# Visit: /contacts/opt-out-lists
# Visit: /contacts/tags
# Visit: /management/numbers
# Visit: /management/rcs-agent
# Visit: /management/api-connections
# Visit: /messages/send-message
# Visit: /purchase/numbers
# Visit: /account/details
# Visit: /reporting/invoices
```

---

## Step 6: Summary of This Build

| Item | Detail |
|---|---|
| **Module** | Campaign History |
| **URL** | `/messages/campaign-history` |
| **Route name** | `messages.campaign-history` |
| **View file** | `resources/views/quicksms/messages/campaign-history.blade.php` (2,126 lines) |
| **Controller method** | `QuickSMSController::campaignHistory()` (lines 1692-1718) |
| **Backend model** | `app/Models/Campaign.php` (641 lines, complete) |
| **Backend API** | `GET /api/campaigns` via `CampaignApiController@index` (complete) |
| **Current data source** | Mock data (16 hardcoded campaigns) |
| **Next step for Replit** | Replace mock data with `Campaign::where('account_id', ...)->paginate(25)` |
| **Files touched** | 2 files only (`campaign-history.blade.php`, `QuickSMSController.php::campaignHistory()`) |
| **Files frozen** | Everything else (469+ files) |
| **New dependencies** | None |
| **New migrations** | None (all campaign tables already exist from prior builds) |
| **Risk to other modules** | Zero — this build adds/modifies UI for a single page only |

---

## FINAL WARNING TO REPLIT AGENT

**Your job is to merge and verify. Then optionally wire mock data to real data. That's it.**

- If you feel the urge to "clean up" another file → **STOP**
- If you feel the urge to add a migration → **STOP**
- If you feel the urge to refactor a service → **STOP**
- If you feel the urge to modify routes → **STOP**
- If you feel the urge to improve a controller you weren't asked to touch → **STOP**
- If a page other than campaign-history has an issue → **do NOT fix it in this merge**

**One build. One module. Zero drift.**

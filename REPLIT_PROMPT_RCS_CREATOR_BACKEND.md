# Replit Prompt: RCS Creator — Pull Backend & Wire UI

## Overview

A complete RCS Agent registration, approval workflow, and media asset backend has been built on branch `claude/quicksms-security-performance-dr8sw`. Your job is to pull this branch and wire any remaining frontend pieces to these backend API endpoints. **Do not rebuild anything that already exists.** The backend is fully functional — you are connecting the UI layer to it.

Pull the branch first:
```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git checkout claude/quicksms-security-performance-dr8sw
git pull origin claude/quicksms-security-performance-dr8sw
php artisan migrate
```

This creates/updates 5 tables (`rcs_agents`, `rcs_agent_assignments`, `rcs_agent_status_histories`, `rcs_agent_comments`, `rcs_assets`) with PostgreSQL enums, RLS policies, UUID triggers, and indexes.

---

## What Was Built (Backend — Already Complete)

### Database Tables (14 migrations in `database/migrations/`)

| Table | Purpose |
|---|---|
| `rcs_agents` | Full agent registration: identity, branding, contact, use-case, company details, approver. 11-status state machine with RLS tenant isolation |
| `rcs_agent_assignments` | Polymorphic assignments (User or SubAccount) controlling who can use an approved agent |
| `rcs_agent_status_histories` | Immutable audit trail of every status transition with payload snapshots, IP, user-agent |
| `rcs_agent_comments` | Customer-visible and admin-internal comments on agent requests |
| `rcs_assets` | RCS media uploads/URL imports with edit params (zoom, crop, orientation), draft sessions |

### Models (5 in `app/Models/`)

- **`RcsAgent`** — Tenant-scoped (global scope + RLS), 11-status state machine with `TRANSITIONS` map, `transitionTo()` method, `toPortalArray()` / `toAdminArray()` serialisation, `scopeUsableByUser()` for assignment-aware queries
- **`RcsAgentAssignment`** — Polymorphic (`assignable_type` = User or SubAccount), links agents to specific users/sub-accounts
- **`RcsAgentComment`** — Customer-visible vs admin-internal comments (`comment_type`), `scopeCustomerVisible()`
- **`RcsAgentStatusHistory`** — Immutable audit log per transition with `from_status`, `to_status`, `action`, `payload_snapshot`
- **`RcsAsset`** — Media asset with `source_type` (upload/url), `edit_params` JSON, `draft_session` for ephemeral assets

### Controllers (3)

- **`RcsAgentController`** (`app/Http/Controllers/`) — Customer portal: list, create wizard view, edit wizard view, API CRUD, submit, provide-info, resubmit, approved list, delete
- **`RcsAgentApprovalController`** (`app/Http/Controllers/Admin/`) — Admin: list with filters, show detail, start review, approve, reject, request info, send to supplier, supplier approved, mark live, suspend, reactivate, revoke
- **`RcsAssetController`** (`app/Http/Controllers/Api/`) — Media pipeline: process-url, process-upload, proxy-image, update crop/edit params, finalize

### Services (2 in `app/Services/`)

- **`RcsAssetService`** — Image processing pipeline: download from URL or handle upload, validate MIME type, store to disk, generate thumbnails, manage draft sessions
- **`RcsContentValidator`** — Validates RCS message content structure (rich cards, carousels, buttons)

### Views (Already Built)

- **`rcs-agent.blade.php`** — Agent Library page (list view with status badges, search, sort, actions — already calls `/api/rcs-agents` via `fetch()`)
- **`rcs-agent-wizard.blade.php`** — 7-step registration wizard (already calls `/api/rcs-agents` via `$.ajax()` for save/update/submit)

### JavaScript (Already Built)

- **`public/js/rcs-wizard.js`** (3,460 lines) — Shared RCS content builder (rich cards, carousels, media crop, buttons)
- **`public/js/rcs-preview-renderer.js`** (404 lines) — Phone frame preview renderer

---

## API Endpoints — Customer Portal

All endpoints require `customer.auth` middleware and return JSON.

### RCS Agent CRUD

| Method | Endpoint | Purpose | Key Details |
|--------|----------|---------|-------------|
| `GET` | `/api/rcs-agents` | List all agents for account | Returns `{ success, data: [...toPortalArray()] }` |
| `GET` | `/api/rcs-agents/approved` | Approved agents usable by current user | Respects assignment rules (user/sub-account scoping) |
| `POST` | `/api/rcs-agents` | Create new agent (draft or submit) | Body includes all agent fields. Add `submit: true` to auto-submit |
| `GET` | `/api/rcs-agents/{uuid}` | Get single agent detail | Returns `{ data, assignments, comments, return_info }` |
| `PUT` | `/api/rcs-agents/{uuid}` | Update draft/rejected agent | Only works when `isEditable()` (draft, rejected, pending_info) |
| `DELETE` | `/api/rcs-agents/{uuid}` | Soft-delete draft agent | Only draft status allowed |
| `POST` | `/api/rcs-agents/{uuid}/submit` | Submit for review | Validates all required fields before transition |
| `POST` | `/api/rcs-agents/{uuid}/provide-info` | Respond to admin info request | Body: `{ additional_info }`. Creates comment + resolves notifications |
| `POST` | `/api/rcs-agents/{uuid}/resubmit` | Return rejected/returned to draft | Transitions back to draft for re-editing |

### RCS Asset (Media) Pipeline

| Method | Endpoint | Purpose | Key Details |
|--------|----------|---------|-------------|
| `POST` | `/api/rcs/assets/process-url` | Import image from URL | Body: `{ url, edit_params?, draft_session? }` → returns `{ asset: { uuid, public_url } }` |
| `POST` | `/api/rcs/assets/process-upload` | Upload image file | Multipart: `file` (jpeg/png/gif, max 1MB) + `edit_params?` + `draft_session?` |
| `POST` | `/api/rcs/assets/proxy-image` | Proxy external image (SSRF-safe) | Body: `{ url }` → returns `{ dataUrl, contentType, size }` |
| `PUT` | `/api/rcs/assets/{uuid}` | Update crop/edit params | Body: `{ edit_params: { zoom, crop_position, orientation } }` |
| `POST` | `/api/rcs/assets/{uuid}/finalize` | Mark asset as non-draft | Removes from cleanup queue, returns final `{ uuid, public_url }` |

---

## API Endpoints — Admin Console

All endpoints require admin authentication middleware.

### RCS Agent Approval Workflow

| Method | Endpoint | Purpose | Body |
|--------|----------|---------|------|
| `GET` | `/admin/api/rcs-agents` | List all agents (filterable) | Query: `?status=&account_id=&billing_category=&use_case=` |
| `GET` | `/admin/api/rcs-agents/{uuid}` | Agent detail with full admin data | Returns `toAdminArray()` + account info + status history |
| `POST` | `/admin/api/rcs-agents/{uuid}/review` | Start review (submitted → in_review) | `{ notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/approve` | Approve (in_review → approved) | `{ notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/approve-and-submit` | Approve + send to supplier | `{ notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/reject` | Reject with reason | `{ reason, notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/request-info` | Return to customer | `{ reason, notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/supplier-approved` | Mark supplier approved | `{ notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/mark-live` | Final approval (→ approved/live) | `{ notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/suspend` | Suspend live agent | `{ reason, notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/reactivate` | Reactivate suspended | `{ notes? }` |
| `POST` | `/admin/api/rcs-agents/{uuid}/revoke` | Permanently revoke | `{ reason, notes? }` |

---

## RCS Agent Status Machine (11 States)

```
draft → submitted → in_review → sent_to_supplier → supplier_approved → approved (Live)
                  ↘ pending_info ↔ info_provided ↗
                  ↘ rejected → (resubmit) → draft
         approved → suspended → approved (reactivate)
                              → revoked (terminal)
```

**Customer actions:** submit, provide-info, resubmit (to draft)
**Admin actions:** review, approve, reject, request-info, send-to-supplier, supplier-approved, mark-live, suspend, reactivate, revoke

---

## RCS Agent Fields (Store/Update Payload)

```json
{
    "name": "string|max:25 (required)",
    "description": "string|max:100",
    "brand_color": "hex string, default #886CC0",
    "logo_url": "string (from asset pipeline)",
    "logo_crop_metadata": "{ zoom, offsetX, offsetY, ... }",
    "hero_url": "string (from asset pipeline)",
    "hero_crop_metadata": "{ zoom, offsetX, offsetY, ... }",
    "support_phone": "string|max:20",
    "website": "string|max:255",
    "support_email": "email",
    "privacy_url": "url|max:500",
    "terms_url": "url|max:500",
    "show_phone": "boolean (default true)",
    "show_website": "boolean (default true)",
    "show_email": "boolean (default true)",
    "billing_category": "conversational|non-conversational",
    "use_case": "otp|transactional|promotional|multi-use",
    "campaign_frequency": "string|max:50",
    "monthly_volume": "string|max:50",
    "opt_in_description": "text|max:5000",
    "opt_out_description": "text|max:5000",
    "use_case_overview": "text|max:5000",
    "test_numbers": "array of strings",
    "company_number": "string|max:20",
    "company_website": "string|max:255",
    "registered_address": "string|max:2000",
    "approver_name": "string|max:100",
    "approver_job_title": "string|max:100",
    "approver_email": "email|max:255",
    "sector": "string|max:100",
    "sub_account_ids": "array of UUIDs (assignment)",
    "user_ids": "array of UUIDs (assignment)",
    "submit": "boolean (auto-submit on create)"
}
```

---

## Routes (Already Registered in `routes/web.php`)

### Customer Portal Views
```
GET  /management/rcs-agent              → QuickSMSController@rcsAgentRegistrations
GET  /management/rcs-agent/create       → QuickSMSController@rcsAgentCreate
GET  /management/rcs-agent/{uuid}/edit  → QuickSMSController@rcsAgentEdit
```

### Customer API (JSON)
```
GET    /api/rcs-agents                  → RcsAgentController@list
GET    /api/rcs-agents/approved         → RcsAgentController@approved
POST   /api/rcs-agents                  → RcsAgentController@store
GET    /api/rcs-agents/{uuid}           → RcsAgentController@show
PUT    /api/rcs-agents/{uuid}           → RcsAgentController@update
DELETE /api/rcs-agents/{uuid}           → RcsAgentController@destroy
POST   /api/rcs-agents/{uuid}/submit    → RcsAgentController@submit
POST   /api/rcs-agents/{uuid}/provide-info → RcsAgentController@provideInfo
POST   /api/rcs-agents/{uuid}/resubmit  → RcsAgentController@resubmit
```

### RCS Asset API
```
POST   /api/rcs/assets/process-url      → RcsAssetController@processUrl
POST   /api/rcs/assets/process-upload   → RcsAssetController@processUpload
POST   /api/rcs/assets/proxy-image      → RcsAssetController@proxyImage
PUT    /api/rcs/assets/{uuid}           → RcsAssetController@updateAsset
POST   /api/rcs/assets/{uuid}/finalize  → RcsAssetController@finalizeAsset
```

### Admin API
```
GET    /admin/api/rcs-agents            → RcsAgentApprovalController@index
GET    /admin/api/rcs-agents/{uuid}     → RcsAgentApprovalController@show
POST   /admin/api/rcs-agents/{uuid}/review          → startReview
POST   /admin/api/rcs-agents/{uuid}/approve          → approve
POST   /admin/api/rcs-agents/{uuid}/approve-and-submit → approveAndSubmitToSupplier
POST   /admin/api/rcs-agents/{uuid}/reject           → reject
POST   /admin/api/rcs-agents/{uuid}/request-info     → requestInfo
POST   /admin/api/rcs-agents/{uuid}/supplier-approved → supplierApproved
POST   /admin/api/rcs-agents/{uuid}/mark-live        → markLive
POST   /admin/api/rcs-agents/{uuid}/suspend          → suspend
POST   /admin/api/rcs-agents/{uuid}/reactivate       → reactivate
POST   /admin/api/rcs-agents/{uuid}/revoke           → revoke
```

---

## What Still Needs Wiring

### 1. Send Message Page — Replace Mock RCS Agents

In `QuickSMSController@sendMessage()` (line ~358), mock `$rcs_agents` data needs replacing with a real database query:

```php
// REPLACE THIS:
$rcs_agents = [
    ['id' => 1, 'name' => 'QuickSMS Brand', ...],
    ['id' => 2, 'name' => 'Promotions Agent', ...],
];

// WITH THIS:
$user = User::withoutGlobalScope('tenant')->find(session('customer_user_id'));
$rcs_agents = $user ? RcsAgent::usableByUser($user)->get()->map(fn($a) => [
    'id' => $a->id,
    'uuid' => $a->uuid,
    'name' => $a->name,
    'logo' => $a->logo_url ?? asset('images/default-agent-logo.png'),
    'tagline' => $a->description ?? '',
    'brand_color' => $a->brand_color ?? '#886CC0',
    'status' => 'approved',
])->toArray() : [];
```

### 2. Confirm Campaign Page — Replace Mock RCS Agent

In `QuickSMSController@confirmCampaign()` (line ~458), the `rcs_agent` data in the channel array needs to pull from the database using the session-stored agent selection.

### 3. Templates Page — Replace Mock RCS Agents

In `QuickSMSController@templates()` (line ~1692), mock `$rcs_agents` also needs replacing with the same pattern.

---

## Key Architecture Notes

1. **All responses** follow `{ success: true/false, data: {...} }` format
2. **Tenant isolation** — `rcs_agents` has PostgreSQL RLS + Eloquent global scope on `account_id`
3. **State machine** — Use `$agent->transitionTo($newStatus, $userId, $reason, $notes, $actingUser)` — never set `workflow_status` directly
4. **Assignment scoping** — `RcsAgent::usableByUser($user)` checks: approved status + (no assignments = available to all, OR assigned to user/sub-account)
5. **Media pipeline** — Upload/URL → process → get UUID → use `logo_url`/`hero_url` in agent. Finalize to persist.
6. **Admin notes** — `admin_notes` field is in `$hidden` on the model; never exposed to customer portal
7. **Notifications** — Agent transitions create `AdminNotification` and customer `Notification` records automatically
8. **Governance audit** — Status transitions log to `governance_audit_events` table

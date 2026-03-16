# Flow Builder Phase 2 — Complete Reference & Anti-Drift Prompt

> **Purpose**: Definitive reference document for the Flow Builder Phase 2 implementation.
> Covers the full scope of what was built, how it works, every commit that shaped it,
> the security guardrails applied, and anti-drift rules to prevent regression.
>
> **Branch**: `claude/quicksms-flow-builder-XtahH`
> **Base**: `master`

---

## Table of Contents

1. [Commit History](#1-commit-history)
2. [Architecture Overview](#2-architecture-overview)
3. [Database Schema](#3-database-schema)
4. [Backend (Laravel)](#4-backend-laravel)
5. [Frontend (Vanilla JS Canvas Engine)](#5-frontend-vanilla-js-canvas-engine)
6. [Security Guardrails](#6-security-guardrails)
7. [Anti-Drift Rules](#7-anti-drift-rules)
8. [Verification Checklist](#8-verification-checklist)
9. [Known Limitations](#9-known-limitations-phase-3-backlog)

---

## 1. Commit History

### Phase 2 Commits (newest first)

| Commit | Description |
|--------|-------------|
| `ad94cb3` | **Fix permission blocker + step validation**: add `manage_api_credentials` to all 7 role defaults in `User.php`, add server-side validation for `action_group` step types (8 allowed), document known Phase 3 limitations |
| `2d43326` | Add `FLOW_BUILDER_PHASE2.md` — comprehensive reference doc with anti-drift guardrails |
| `cab954f` | **Fix remaining review issues**: restore `account_id`/`created_by` to `$fillable`, add `permission:manage_api_credentials` middleware to write routes, add CSRF meta tag presence check, add `AccountAuditLog` recording for credential CRUD |
| `670beac` | Add `REPLIT_PHASE2_PROMPT.md` — the original implementation spec |
| `eeeef27` | **Fix code review findings**: node type `in:` validation whitelist, `bootstrap.Modal.getOrCreateInstance()`, `classList.add/remove('d-none')` instead of `style.display` |
| `5c28714` | **Phase 2 feature commit**: 21 node types, credentials vault, Action Group node, decision nodes, dynamic data loading, dual-port webhook branching |

### What Each Fix Addressed

**`eeeef27` fixed (against `5c28714`)**:
- Replaced `'required|string|max:50'` node type validation with `'required|in:...'` whitelist of all 21 types
- Replaced 4 instances of `new bootstrap.Modal(modalEl)` with `bootstrap.Modal.getOrCreateInstance(modalEl)`
- Replaced 14 instances of `style.display` manipulation with `classList.add/remove('d-none')`

**`cab954f` fixed (against `eeeef27`)**:
- `account_id` and `created_by` were removed from `$fillable` in `eeeef27` but the controller's `create()` call still passes them — silently dropped by mass assignment protection, causing DB NOT NULL failures. **Restored to `$fillable`** (safe because controller sets them from session, not request)
- Added `permission:manage_api_credentials` middleware to POST/PUT/DELETE credential routes (GET remains open for dropdown population)
- Added CSRF meta tag presence check before credential save fetch call
- Added `AccountAuditLog::record()` audit trail to credential store/update/destroy

**`ad94cb3` fixed (against `cab954f`)**:
- `manage_api_credentials` permission was used in route middleware but didn't exist in `User::ROLE_DEFAULT_PERMISSIONS`. Since `hasPermission()` returns `$permissions[$permission] ?? false`, all non-owner users got 403 on credential writes. **Added to all 7 role arrays** mirroring `manage_api_connections` grants (owner/admin/developer: true, others: false)
- `action_group` nodes accepted arbitrary step types server-side (`nodes.*.config` validated only as `nullable|array`). **Added validation** that each `steps[].type` is in the 8 allowed step types
- Documented known Phase 3 limitations for `trigger_campaign`, `trigger_contact_event`, `flow_handoff`

---

## 2. Architecture Overview

### Tech Stack
- **Backend**: Laravel (PHP), PostgreSQL with UUID primary keys, Row Level Security (RLS)
- **Frontend**: Vanilla JavaScript canvas engine (no React/Vue), Bootstrap 5
- **Auth**: Session-based via `customer.auth` middleware, tenant isolation via `session('customer_tenant_id')`
- **Permissions**: Role-based via `User::ROLE_DEFAULT_PERMISSIONS` merged with per-user `permission_toggles`, enforced by `CheckPermission` middleware

### File Map

```
app/
  Models/
    Flow.php                    # Flow model (has nodes, connections)
    FlowNode.php                # Node model with type→category map (21 types)
    FlowConnection.php          # Connection model (source → target, source_handle)
    ApiCredential.php           # Credentials vault (encrypted:array, $hidden)
    User.php                    # Role permissions including manage_api_credentials
  Http/Controllers/
    FlowBuilderController.php   # Flow CRUD + canvas save/load + step validation
    ApiCredentialController.php  # Credential CRUD with audit logging
  Http/Middleware/
    CheckPermission.php         # Enforces permission:xxx middleware (owners bypass)

database/migrations/
    2026_03_11_000010_create_flows_table.php
    2026_03_11_000011_create_flow_nodes_table.php
    2026_03_11_000012_create_flow_connections_table.php
    2026_03_13_100010_create_api_credentials_table.php

resources/views/quicksms/flows/
    index.blade.php             # Flow list page
    builder.blade.php           # Visual canvas + palette + modals (514 lines)

public/
    css/flow-builder.css        # All flow builder styles (1039 lines)
    js/flow-builder.js          # Canvas engine (3060 lines)

routes/web.php                  # Flow + credential routes (lines 126-145)
```

### Data Flow

```
Browser Canvas                    Laravel Controller              PostgreSQL
─────────────                    ──────────────────              ──────────
User drags nodes,     ──POST──>  FlowBuilderController::save()   ──TX──>  flows
draws connections,               validates all 21 node types              flow_nodes
configures properties            + action_group step types,               flow_connections
                                 atomic delete+recreate in
                                 DB::transaction

Credential modal      ──POST──>  ApiCredentialController::store() ──TX──>  api_credentials
saves via AJAX                   validates auth_type, encrypts            (encrypted:array)
(permission gated)               credentials, records audit log
```

---

## 3. Database Schema

### `flows` table
```sql
id              uuid PRIMARY KEY (auto-generated via trigger)
account_id      uuid NOT NULL → accounts(id)
name            varchar(255)
description     text NULLABLE
status          varchar(20) DEFAULT 'draft'  -- draft|active|paused|archived
version         integer DEFAULT 1
canvas_meta     jsonb NULLABLE  -- {zoom, panX, panY}
created_by      uuid → users(id)
last_activated_at timestamptz NULLABLE
created_at      timestamptz
updated_at      timestamptz
deleted_at      timestamptz NULLABLE
```
RLS: `account_id::text = current_setting('app.current_tenant_id', true)`
UUID trigger: `generate_uuid_flows()`
Grants: `portal_rw` (CRUD), `portal_ro` (SELECT), `svc_red` (ALL), `ops_admin` (ALL)

### `flow_nodes` table
```sql
id              uuid PRIMARY KEY (auto-generated via trigger)
flow_id         uuid NOT NULL → flows(id) ON DELETE CASCADE
node_uid        varchar(64) NOT NULL  -- client-generated unique ID
type            varchar(50) NOT NULL  -- one of 21 allowed types
label           varchar(255) NULLABLE
config          jsonb NULLABLE        -- type-specific configuration
position_x      numeric NOT NULL
position_y      numeric NOT NULL
created_at      timestamptz
updated_at      timestamptz
UNIQUE(flow_id, node_uid)
INDEX(type)
```
RLS: EXISTS subquery on flows table to enforce tenant isolation

### `flow_connections` table
```sql
id              uuid PRIMARY KEY (auto-generated via trigger)
flow_id         uuid NOT NULL → flows(id) ON DELETE CASCADE
source_node_uid varchar(64) NOT NULL
target_node_uid varchar(64) NOT NULL
source_handle   varchar(50) DEFAULT 'default'  -- default|yes|no|success|error
label           varchar(255) NULLABLE
created_at      timestamptz
updated_at      timestamptz
INDEX(flow_id, source_node_uid)
INDEX(flow_id, target_node_uid)
```
RLS: EXISTS subquery on flows table

### `api_credentials` table
```sql
id              uuid PRIMARY KEY (auto-generated via trigger)
account_id      uuid NOT NULL → accounts(id)
name            varchar(100) NOT NULL
auth_type       varchar(20) DEFAULT 'bearer'  -- none|basic|bearer|api_key|custom_header
credentials     text NOT NULL  -- encrypted JSON via Laravel Crypt (encrypted:array cast)
description     text NULLABLE
last_used_at    timestamptz NULLABLE
created_by      uuid NOT NULL → users(id)
created_at      timestamptz
updated_at      timestamptz
deleted_at      timestamptz NULLABLE
```
RLS: `account_id::text = current_setting('app.current_tenant_id', true)`
UUID trigger: `generate_uuid_api_credentials()`
Grants: `portal_rw` (CRUD), `portal_ro` (SELECT), `svc_red` (ALL), `ops_admin` (ALL)

---

## 4. Backend (Laravel)

### Routes (`routes/web.php` lines 126-145)

```
GET    /flows                     FlowBuilderController@index
GET    /flows/builder/{id?}       FlowBuilderController@builder
POST   /flows                     FlowBuilderController@store
PUT    /flows/{id}/save           FlowBuilderController@save
GET    /flows/{id}/load           FlowBuilderController@load
PUT    /flows/{id}/status         FlowBuilderController@updateStatus
POST   /flows/{id}/duplicate      FlowBuilderController@duplicate
DELETE /flows/{id}                FlowBuilderController@destroy

GET    /api-credentials           ApiCredentialController@index
POST   /api-credentials           ApiCredentialController@store        [permission:manage_api_credentials]
PUT    /api-credentials/{id}      ApiCredentialController@update       [permission:manage_api_credentials]
DELETE /api-credentials/{id}      ApiCredentialController@destroy      [permission:manage_api_credentials]
```

All routes require `customer.auth` middleware. Write operations on credentials additionally require `permission:manage_api_credentials`.

### Permission System (`app/Models/User.php`)

The `manage_api_credentials` permission is defined in `ROLE_DEFAULT_PERMISSIONS` for all 7 roles:

| Role | `manage_api_credentials` | Same as `manage_api_connections` |
|------|--------------------------|----------------------------------|
| owner | true | true |
| admin | true | true |
| messaging_manager | false | false |
| finance | false | false |
| developer | true | true |
| user | false | false |
| readonly | false | false |

**How it works:**
- `getEffectivePermissions()` merges role defaults with per-user `permission_toggles` via `array_merge($defaults, $overrides)`
- `hasPermission($permission)` returns `$permissions[$permission] ?? false`
- `CheckPermission` middleware: owners bypass all checks; others need the permission to return true
- No data migration needed for existing users — `array_merge` applies new role defaults automatically, and user overrides only change what was explicitly toggled

### FlowBuilderController (`app/Http/Controllers/FlowBuilderController.php`, 371 lines)

**`builder($id = null)` (lines 34-131)** — Loads the canvas view with all dynamic data:
- `$sender_ids` — approved sender IDs (ordered by is_default desc, value asc); fallback to QuickSMS default
- `$rcs_agents` — RCS agents usable by user (id, name, logo, tagline, brand_color)
- `$contact_lists` — contact lists for the account (id, name)
- `$tags` — tags for the account (id, name)
- `$opt_out_lists` — opt-out lists for the account (id, name)
- `$active_flows` — other flows excluding current (id, name)
- `$api_credentials` — credential metadata only via `class_exists()` check (id, name, auth_type)

All passed to `builder.blade.php` and injected as `window.__flowBuilderData`.

**`save(Request $request, $id)` (lines 160-248)** — Atomic save of entire flow canvas:
1. Validates all nodes against the 21-type whitelist:
   ```php
   'nodes.*.type' => 'required|in:trigger_api,trigger_webhook,trigger_sms_inbound,
       trigger_rcs_inbound,trigger_campaign,trigger_contact_event,trigger_schedule,
       send_message,contact,tag_action,list_action,optout_action,webhook,action_group,
       wait,decision,decision_contact,decision_webhook,inbox_handoff,flow_handoff,end'
   ```
2. Validates `action_group` step types — each `steps[].type` must be in:
   `add_tag, remove_tag, add_to_list, remove_from_list, add_optout, remove_optout, update_contact, wait`
3. Validates connection references: source and target `node_uid` must exist in submitted nodes
4. Wraps in `DB::transaction()`: deletes all existing nodes/connections, re-creates from request
5. Returns success JSON

**`store()` (lines 136-155)** — Creates new flow with `account_id` and `created_by` from session (never from request).

**`load($id)` (lines 253-289)** — Returns flow data as JSON for canvas hydration (nodes + connections mapped).

**`updateStatus($id)` (lines 294-315)** — Sets status to draft|active|paused|archived. Sets `last_activated_at = now()` when activating.

**`duplicate($id)` (lines 320-354)** — Deep-copies flow with all nodes and connections in a transaction. Appends " (Copy)" to name, resets status to draft.

**`destroy($id)` (lines 359-369)** — Soft deletes flow.

### ApiCredentialController (`app/Http/Controllers/ApiCredentialController.php`, 166 lines)

**`index()` (lines 16-36)** — Returns metadata only: id, name, auth_type, description, last_used_at (as `diffForHumans()`), created_at (Y-m-d). **Never returns the `credentials` column** (it's in `$hidden`).

**`store()` (lines 41-81)** — Validates:
- `name` (required, max 100)
- `auth_type` (in: none, basic, bearer, api_key, custom_header)
- `credentials` (required, array)
- `description` (nullable, max 500)

Sets `account_id` from `session('customer_tenant_id')` and `created_by` from `session('customer_user_id')`. Records `AccountAuditLog` entry in try-catch with `AuditContext::actor()`. Returns: id, name, auth_type.

**`update($id)` (lines 86-133)** — Scoped to tenant via `forAccount()`. All fields `sometimes` validated. Tracks old values, calculates diff with credentials redacted as `[REDACTED]`. Records `AccountAuditLog` entry in try-catch.

**`destroy($id)` (lines 138-164)** — Scoped to tenant. Soft deletes. Records `AccountAuditLog` entry in try-catch with credential name and ID.

### Models

**`Flow`** (`app/Models/Flow.php`, 52 lines)
- `$fillable`: account_id, created_by, name, description, status, version, canvas_meta, last_activated_at
- `$casts`: canvas_meta → array, last_activated_at → datetime
- Relationships: `nodes()` hasMany, `connections()` hasMany, `creator()` belongsTo User
- `scopeForAccount($query, $accountId)` for tenant queries

**`FlowNode`** (`app/Models/FlowNode.php`, 77 lines)
- `$fillable`: flow_id, node_uid, type, label, config, position_x, position_y
- `$casts`: config → array, position_x → float, position_y → float
- `getCategory()` maps all 21 types to 4 categories:
  - **trigger** (7): trigger_api, trigger_webhook, trigger_sms_inbound, trigger_rcs_inbound, trigger_campaign, trigger_contact_event, trigger_schedule
  - **action** (7): send_message, contact, tag_action, list_action, optout_action, webhook, action_group
  - **logic** (4): wait, decision, decision_contact, decision_webhook
  - **end** (3): inbox_handoff, flow_handoff, end
- `outgoingConnections()` / `incomingConnections()` via node_uid with flow_id scope

**`FlowConnection`** (`app/Models/FlowConnection.php`, 28 lines)
- `$fillable`: flow_id, source_node_uid, target_node_uid, source_handle, label
- `source_handle` values: `default` (standard), `yes`/`no` (decisions), `success`/`error` (webhook)

**`ApiCredential`** (`app/Models/ApiCredential.php`, 48 lines)
- `$fillable`: account_id, created_by, name, auth_type, credentials, description, last_used_at
- `$casts`: credentials → `encrypted:array` (encrypted at rest via Laravel Crypt)
- `$hidden`: `['credentials']` — never serialized in JSON responses
- `scopeForAccount($query, $accountId)` for tenant queries
- `creator()` belongsTo User

---

## 5. Frontend (Vanilla JS Canvas Engine)

### Blade View (`resources/views/quicksms/flows/builder.blade.php`, 514 lines)

**Layout structure:**
- **Lines 17-59**: Top toolbar — flow name input, status badge, zoom controls, undo/redo, test/save buttons
- **Lines 64-240**: Left sidebar node palette — 4 collapsible sections (Triggers, Actions, Logic, End), each node is `<div class="palette-node" draggable="true" data-type="TYPE">`
- **Lines 243-256**: Central canvas — SVG layer for connections (`#flow-connections-svg`), nodes layer (`#flow-nodes-layer`), empty state
- **Lines 259-274**: Right sidebar properties panel (`#flow-properties`) — rendered dynamically by JS
- **Lines 299-309**: Data injection:
  ```javascript
  window.__flowBuilderData = {
      senderIds:      @json($sender_ids),
      rcsAgents:      @json($rcs_agents),
      contactLists:   @json($contact_lists ?? []),
      tags:           @json($tags ?? []),
      optOutLists:    @json($opt_out_lists ?? []),
      activeFlows:    @json($active_flows ?? []),
      apiCredentials: @json($api_credentials ?? [])
  };
  ```
- **Lines 315-333**: Message composer modal (`#flowMessageComposerModal`) — iframe embed
- **Lines 338-358**: Message preview modal (`#flowPreviewModal`) — SMS/RCS toggle
- **Lines 363-410**: Keyword management modal (`#flowKeywordModal`)
- **Lines 415-484**: Credential modal (`#flowCredentialModal`) — auth type selector, conditional field groups per type, error display area, save button
- **Lines 496-511**: FlowBuilder initialization with options

### Node Type Registry (21 types in `flow-builder.js` lines 12-269)

#### Triggers (7) — `inputs: false`, `outputs: ['default']`

| Type | Label | Icon | Config Fields |
|------|-------|------|---------------|
| `trigger_api` | API Trigger | fa-plug | endpoint_note (info), variables (textarea) |
| `trigger_webhook` | External Webhook | fa-satellite-dish | webhook_url_note (info), payload_schema (textarea), auth_method (select: none/hmac), hmac_secret (text, showWhen hmac) |
| `trigger_sms_inbound` | SMS Inbound | fa-comment-dots | sender_id (select, dynamic: senderIds), match_type (select: any/keyword/contains/regex), keywords (text, showWhen keyword/contains) |
| `trigger_rcs_inbound` | RCS Inbound | fa-hand-pointer | rcs_agent_id (select, dynamic: rcsAgents), match_type (select: any/postback/text), postback_data/text_match (conditional) |
| `trigger_campaign` | Campaign Event | fa-bullhorn | campaign_event (select: 5 events), campaign_id (text) |
| `trigger_contact_event` | Contact Event | fa-address-book | event_type (select: 8 events), filter_list_id/filter_tag_id (conditional dynamic selects) |
| `trigger_schedule` | Schedule | fa-clock | schedule_type (select: once/daily/weekly/monthly), time, date |

#### Actions (7) — `inputs: true`

| Type | Label | Icon | Outputs | Custom Properties |
|------|-------|------|---------|-------------------|
| `send_message` | Send Message | fa-paper-plane | ['default'] (dynamic) | Yes — iframe message composer |
| `contact` | Contact | fa-user-plus | ['default'] | No — action, phone, name, email fields |
| `tag_action` | Tag | fa-tag | ['default'] | No — action (add/remove), tag_name |
| `list_action` | List | fa-list | ['default'] | No — action (add/remove), list_id (dynamic) |
| `optout_action` | Opt-Out | fa-ban | ['default'] | No — action (add/remove), opt_out_list_id (dynamic), reason |
| `webhook` | Webhook | fa-globe | **['success', 'error']** | Yes — URL, method, headers, body, credential, timeout |
| `action_group` | Quick Steps | fa-layer-group | ['default'] | Yes — ordered list of steps |

#### Logic (4) — `inputs: true`

| Type | Label | Icon | Outputs | Custom Properties |
|------|-------|------|---------|-------------------|
| `wait` | Wait / Delay | fa-hourglass-half | ['default'] | No — wait_type, duration_value, duration_unit, quiet_hours |
| `decision` | Decision | fa-random | **['yes', 'no']** | No — condition_type, field, operator, compare_value, timeout |
| `decision_contact` | Contact Decision | fa-address-card | **['yes', 'no']** | Yes — condition selector with dynamic lists/tags/optout |
| `decision_webhook` | Webhook Decision | fa-code-branch | **['yes', 'no']** | Yes — source webhook, condition_type, json_path, operator, compare_value |

#### End (3) — `inputs: true`, `outputs: []`

| Type | Label | Icon | Config |
|------|-------|------|--------|
| `inbox_handoff` | Inbox Handoff | fa-headset | assign_to, priority, note |
| `flow_handoff` | Flow Handoff | fa-exchange-alt | target_flow_id (dynamic: activeFlows), pass_context (checkbox) |
| `end` | End Flow | fa-stop-circle | none |

### Custom Property Renderers

**`_renderWebhookProperties(node)`** (line ~2603):
- URL input, method select (GET/POST/PUT/DELETE)
- Dynamic headers (key-value pairs, add/remove)
- Request body textarea (shown for POST/PUT)
- Credential selector dropdown (populated from `__flowBuilderData.apiCredentials`)
- "Create New Credential" option opens the credential modal via `_openCredentialModal()`
- Timeout and retries inputs
- Response variable name for downstream use

**`_renderActionGroupProperties(node)`** (line ~2486):
- Ordered list of steps stored in `node.config.steps[]`
- Each step: type selector + type-specific fields rendered as numbered cards
- 8 step types defined in `ACTION_GROUP_STEP_TYPES` (line ~2472):
  - `add_tag` / `remove_tag` — tag_name field
  - `add_to_list` / `remove_from_list` — list_id (dynamic: contactLists)
  - `add_optout` / `remove_optout` — opt_out_list_id (dynamic: optOutLists)
  - `update_contact` — field_name + field_value
  - `wait` — duration value + unit (minutes/hours/days)
- "Add Step" button appends new steps; X button removes steps
- **Server-side validated**: each step type checked against allowed list in `FlowBuilderController::save()`

**`_renderDecisionContactProperties(node)`** (line ~2716):
- Condition type select: is_contact, not_contact, in_list, not_in_list, has_tag, not_has_tag, is_opted_out, not_opted_out
- Conditional fields toggle with `classList.add/remove('d-none')`:
  - List selector for in_list/not_in_list (dynamic: contactLists)
  - Tag input for has_tag/not_has_tag (dynamic: tags)
  - Opt-out selector for is_opted_out/not_opted_out (dynamic: optOutLists)

**`_renderDecisionWebhookProperties(node)`** (line ~2802):
- Source webhook selector (links to upstream webhook node)
- Condition type: status_code, json_path_equals, json_path_contains, json_path_exists, response_empty
- JSON path input (shown when condition starts with "json_path")
- Compare value input (hidden for json_path_exists, response_empty)

### Dual Output Ports

**Webhook node** renders two output ports:
- **Success port** (`.port-output-success`): positioned at `left:30%`, green border (`#2e7d32`)
- **Error port** (`.port-output-error`): positioned at `left:70%`, red border (`#c62828`)
- Branch labels: "success" (green `#e8f5e9` bg) and "error" (red `#ffebee` bg)
- Connections from these ports use `source_handle: 'success'` or `source_handle: 'error'`

**Decision nodes** (`decision`, `decision_contact`, `decision_webhook`) have `yes`/`no` dual output ports:
- **Yes port** (`.port-output-yes`): `left:30%`, green styling
- **No port** (`.port-output-no`): `left:70%`, red styling
- Branch labels: "yes" (green) and "no" (red)

### Credential Modal

- Modal ID: `#flowCredentialModal`
- Opened via `_openCredentialModal(nodeId)` (line ~2937)
- Uses `bootstrap.Modal.getOrCreateInstance(modalEl)` — **never `new bootstrap.Modal()`**
- Auth type selector (`#credentialAuthType`) toggles field groups using `_toggleCredentialFields()`:
  - `basic` → `#credentialFields-basic` (username + password)
  - `bearer` → `#credentialFields-bearer` (token)
  - `api_key` → `#credentialFields-api_key` (header name + key)
  - `custom_header` → `#credentialFields-custom_header` (header name + value)
  - `none` → no fields shown
- Visibility toggled with `classList.add/remove('d-none')` — **never `style.display`**
- CSRF token: read from `meta[name="csrf-token"]` — if missing, shows "Session expired. Please refresh the page." in `#credentialError`
- On save success (`_saveCredential`, line ~2975):
  1. POSTs to `/api-credentials` with name, auth_type, credentials object, description
  2. Adds new credential to `window.__flowBuilderData.apiCredentials`
  3. Updates the webhook node's credential dropdown
  4. Auto-selects the newly created credential
  5. Closes modal
- Error display: `#credentialError` element, toggled with `d-none`

### Dynamic Data Loading

The Blade view injects server data into `window.__flowBuilderData`:
```javascript
window.__flowBuilderData = {
    senderIds:      [...],  // {id, name, type}
    rcsAgents:      [...],  // {id, name, logo, tagline, brand_color}
    contactLists:   [...],  // {id, name}
    tags:           [...],  // {id, name}
    optOutLists:    [...],  // {id, name}
    activeFlows:    [...],  // {id, name}
    apiCredentials: [...]   // {id, name, auth_type}
};
```

Config fields with `dynamic: 'keyName'` auto-populate their `<select>` options from this data.
Fields with `showWhen: { key: 'other_field', values: ['val1', 'val2'] }` toggle visibility based on the referenced field's value.

### Templates (3 built-in, lines ~274-331)

| Template | Nodes | Key Features |
|----------|-------|--------------|
| `welcome` | API Trigger → Send Message → Wait → Send Message → Tag Action → End | Basic onboarding sequence |
| `reminder` | API Trigger → Wait → Send Message → Decision → Tag/Fallback → End | Decision branching (yes/no) |
| `delivery` | API Trigger → Send Message → Decision → Webhook/Handoff → End | Webhook dual-port + inbox handoff |

### Canvas Engine Key Methods (`flow-builder.js`)

| Method | Line | Purpose |
|--------|------|---------|
| `addNode(type, x, y, config, label, nodeUid)` | ~435 | Creates node from type definition, adds to DOM and model |
| `_renderNode(node)` | ~571 | Renders node card HTML with ports, preview, drag handles |
| `_renderOutputPorts(el, node)` | ~673 | Renders output ports (single, dual yes/no, dual success/error) |
| `_getConfigPreview(node)` | ~773 | Returns short preview text for canvas card |
| `_refreshNode(nodeId)` | ~842 | Updates node preview without full re-render |
| `_fullRebuildNode(nodeId)` | ~865 | Complete re-render (for dynamic output changes) |
| `deleteNode(nodeId)` | ~889 | Removes node and all its connections |
| `_selectNode(nodeId)` | ~967 | Selects node, shows properties panel |
| `_showProperties(nodeId)` | ~1045 | Routes to correct property renderer based on type |
| `_save()` | ~1710 | Serializes nodes/connections/canvas_meta, PUT to save endpoint |
| `_renderAllConnections()` | — | Redraws all SVG bezier connection paths |
| `_drawConnection(connObj)` | — | Draws single connection with bezier curves |
| `_renderActionGroupProperties(node)` | ~2486 | Renders step list with add/remove |
| `_bindActionGroupEvents(node, nodeId)` | ~2545 | Event binding for step management |
| `_renderWebhookProperties(node)` | ~2603 | Renders webhook config form |
| `_bindWebhookEvents(node, nodeId)` | ~2678 | Event binding for webhook fields |
| `_renderDecisionContactProperties(node)` | ~2716 | Renders contact condition selector |
| `_bindDecisionContactEvents(node, nodeId)` | ~2769 | Toggles conditional field visibility |
| `_renderDecisionWebhookProperties(node)` | ~2802 | Renders webhook condition selector |
| `_bindDecisionWebhookEvents(node, nodeId)` | ~2860 | Toggles conditional visibility |
| `_bindCommonPropertyEvents(node, nodeId)` | ~2896 | Label, description, delete, close handlers |
| `_openCredentialModal(nodeId)` | ~2937 | Opens credential modal, sets up handlers |
| `_toggleCredentialFields(authType)` | ~2967 | Shows/hides credential field groups |
| `_saveCredential(nodeId)` | ~2975 | AJAX POST credential, updates dropdown |

### CSS Highlights (`public/css/flow-builder.css`, 1039 lines)

**Decision branch labels** (lines ~548-573):
```css
.branch-label { position: absolute; font-size: 0.65rem; font-weight: 700; }
.branch-label.yes { left: 20%; background: #e8f5e9; color: #2e7d32; }
.branch-label.no  { left: 60%; background: #ffebee; color: #c62828; }
```

**Action group step cards** (lines ~919-977):
```css
.action-group-step { background: #f8f8f8; border-radius: 8px; padding: 10px 12px; }
.step-number { width: 22px; height: 22px; border-radius: 50%; background: #886CC0; color: #fff; }
.step-drag-handle { cursor: grab; }
.step-remove:hover { color: #c62828; }
```

**Webhook dual ports** (lines ~980-1007):
```css
.node-port.port-output-success { bottom: -7px; left: 30%; border-color: #2e7d32; }
.node-port.port-output-error   { bottom: -7px; left: 70%; border-color: #c62828; }
.branch-label.success { left: 20%; background: #e8f5e9; color: #2e7d32; }
.branch-label.error   { left: 60%; background: #ffebee; color: #c62828; }
```

---

## 6. Security Guardrails

### Tenant Isolation
- All queries scoped by `session('customer_tenant_id')`: `Flow::where('account_id', $accountId)`
- PostgreSQL RLS enforces tenant isolation at the database level as a second boundary
- `ApiCredential::forAccount($accountId)` scope used for all credential queries

### Mass Assignment
- `account_id` and `created_by` ARE in `$fillable` on both `Flow` and `ApiCredential`
- Controllers set them **explicitly from session**, never from request input:
  ```php
  'account_id' => session('customer_tenant_id'),
  'created_by' => session('customer_user_id'),
  ```
- Validation rules do NOT include `account_id` or `created_by`, so even if a malicious request body included them, `$request->validate()` would ignore them and the controller's explicit session values take precedence in the `create()` array
- This is the project-wide pattern: Flow, SubAccount, ApiConnection, Contact all include `account_id` in `$fillable`

### Credential Encryption
- `credentials` column uses `'encrypted:array'` cast — encrypted at rest via Laravel's `Crypt` facade
- `$hidden = ['credentials']` — never serialized in JSON responses
- `index()` endpoint returns only metadata: id, name, auth_type, description, last_used_at, created_at
- Audit log diffs redact credentials: `['from' => '[REDACTED]', 'to' => '[REDACTED]']`

### Permission Gating
- Credential write routes (POST/PUT/DELETE) require `permission:manage_api_credentials`
- Credential read (GET) open to all authenticated users (needed for dropdown population in webhook nodes)
- `manage_api_credentials` exists in all 7 role defaults in `User::ROLE_DEFAULT_PERMISSIONS` (added in `ad94cb3`):
  - **Granted**: owner, admin, developer
  - **Denied**: messaging_manager, finance, user, readonly
- `CheckPermission` middleware: owners automatically bypass all checks; others checked via `hasPermission()`
- `getEffectivePermissions()` merges role defaults with stored overrides — new permissions propagate to existing users automatically

### Audit Trail
- All credential CRUD operations record `AccountAuditLog` entries via `AuditContext::actor()` (user_id, user_name)
- Audit logging wrapped in try-catch — audit failures never break the primary operation
- Store logs: action type + credential name + auth_type
- Update logs: action type + changed field diff (credentials redacted)
- Destroy logs: action type + credential name + ID

### CSRF Protection
- All fetch calls include `X-CSRF-TOKEN` header from `meta[name="csrf-token"]`
- Missing CSRF meta tag shows "Session expired. Please refresh the page." instead of silent failure

### Input Validation
- Node types validated against explicit whitelist of 21 types via `in:` rule (no arbitrary strings)
- Action group step types validated server-side: each `steps[].type` must be in 8 allowed values
- Connection references validated: source and target `node_uid` must exist in the submitted nodes array
- Credential `auth_type` validated: `in:none,basic,bearer,api_key,custom_header`
- All string fields have max length constraints

---

## 7. Anti-Drift Rules

These rules MUST be followed by any AI agent or developer modifying Phase 2 code. Violating them is a regression.

### DO

1. **Use `bootstrap.Modal.getOrCreateInstance(modalEl)`** for all modal instantiation
2. **Use `classList.add('d-none')` / `classList.remove('d-none')`** for all visibility toggling
3. **Keep `account_id` and `created_by` in `$fillable`** on models where the controller explicitly sets them from session — this is the project-wide pattern (Flow, ApiCredential, SubAccount, ApiConnection, Contact all do this)
4. **Validate node types with `'required|in:...'`** listing all 21 types explicitly — never use `'string|max:50'`
5. **Validate action_group step types** against the 8 allowed values server-side
6. **Scope all queries to tenant** using `session('customer_tenant_id')`
7. **Record `AccountAuditLog`** for any new CRUD operations on sensitive resources
8. **Wrap audit logging in try-catch** — audit failures must not break the primary operation
9. **Read CSRF token from `meta[name="csrf-token"]`** and check for presence before fetch calls
10. **Use `permission:` middleware** on write routes for sensitive resources, and ensure the permission exists in `User::ROLE_DEFAULT_PERMISSIONS` for all 7 roles
11. **Keep `credentials` in `$hidden`** and never return raw credential values in API responses

### DO NOT

1. **Never use `new bootstrap.Modal()`** — causes duplicate instantiation issues
2. **Never use `style.display = 'none'`** — use `d-none` class instead
3. **Never use `$request->all()` or `$request->validated()`** when building `create()` arrays that include tenant identifiers — always build the array explicitly
4. **Never add `account_id` to validation rules** — it comes from session, not request
5. **Never return the `credentials` column** in any API response — it contains secrets
6. **Never use `'string|max:50'` for node type validation** — always use the `in:` whitelist
7. **Never skip RLS, UUID triggers, or grants** when creating new tables
8. **Never use `style.display` for showing/hiding elements** in flow-builder.js
9. **Never delete `account_id`/`created_by` from `$fillable`** without also changing how the controller creates records (either switch to `forceFill()` or manual assignment + `save()`)
10. **Never add credential routes without `permission:` middleware** on write operations
11. **Never add a `permission:` middleware reference** without also adding the permission key to all 7 role arrays in `User::ROLE_DEFAULT_PERMISSIONS` — otherwise `hasPermission()` returns false for all non-owner users

### When Adding New Node Types

1. Add the type definition to `NODE_TYPES` in `flow-builder.js` with correct category, outputs, inputs, and configFields
2. Add the type to the `getCategory()` map in `FlowNode.php`
3. Add the type to the `'nodes.*.type' => 'required|in:...'` validation rule in `FlowBuilderController::save()`
4. Add the type to the palette sidebar in `builder.blade.php` in the correct section (Triggers, Actions, Logic, or End)
5. If the node has `customProperties: true`, add a `_render{Type}Properties()` and `_bind{Type}Events()` method pair
6. Add a `_getConfigPreview()` case for the new type
7. **All three locations (JS NODE_TYPES, PHP getCategory(), PHP validation rule) must stay in sync**

### When Adding New Dynamic Data

1. Query in `FlowBuilderController::builder()`, scoped to `$accountId`
2. Pass to the Blade view
3. Add to `window.__flowBuilderData` in the Blade template
4. Reference via `dynamic: 'keyName'` in config field definitions

### When Adding New Permissions

1. Add the permission key to all 7 role arrays in `User::ROLE_DEFAULT_PERMISSIONS`
2. Decide which roles get `true` vs `false` (follow existing patterns like `manage_api_connections`)
3. Use `permission:new_permission_name` in route middleware
4. No data migration needed — `getEffectivePermissions()` auto-merges new defaults

---

## 8. Verification Checklist

### Backend
- [ ] `php -l` passes on all PHP files
- [ ] All 21 types present in `FlowBuilderController::save()` validation rule
- [ ] All 21 types present in `FlowNode::getCategory()` map
- [ ] Action group step types validated server-side (8 allowed types)
- [ ] `ApiCredential.$fillable` includes `account_id` and `created_by`
- [ ] `ApiCredential.$hidden` includes `credentials`
- [ ] Credential routes have `permission:manage_api_credentials` on POST/PUT/DELETE
- [ ] `manage_api_credentials` exists in all 7 role arrays in `User::ROLE_DEFAULT_PERMISSIONS`
- [ ] `grep -c manage_api_credentials app/Models/User.php` returns 7
- [ ] Credential store/update/destroy record `AccountAuditLog` entries
- [ ] Audit logging wrapped in try-catch
- [ ] All flow queries scoped to `session('customer_tenant_id')`
- [ ] Migrations include RLS policy, UUID trigger, and grants

### Frontend
- [ ] Zero instances of `new bootstrap.Modal()` in flow-builder.js
- [ ] Zero instances of `style.display` in flow-builder.js
- [ ] All 21 node types in `NODE_TYPES` object
- [ ] CSRF meta tag presence check before credential save
- [ ] Credential modal toggles fields with `d-none`, not `style.display`
- [ ] Dynamic selects populate from `window.__flowBuilderData`
- [ ] `showWhen` conditional fields toggle correctly
- [ ] Webhook node renders success/error dual ports
- [ ] Action Group renders step list with add/remove
- [ ] Decision Contact shows conditional fields per condition type
- [ ] Decision Webhook shows JSON path fields conditionally

### Integration
- [ ] Save flow round-trips all 21 node types
- [ ] Credential modal saves, adds to dropdown, auto-selects in webhook node
- [ ] Templates load with correct node type keys (tag_action, not tag)
- [ ] Canvas preview text renders for all node types
- [ ] Non-owner admin/developer users can create credentials (not 403)
- [ ] Non-owner messaging_manager/user roles get 403 on credential writes

---

## 9. Known Limitations (Phase 3 Backlog)

| Node / Component | Limitation | Notes |
|------------------|-----------|-------|
| `trigger_campaign` | No runtime event handler | Configurable on canvas and persists to DB, but no backend listener fires this trigger. Phase 3. |
| `trigger_contact_event` | No runtime event handler | Same as above — contact book events don't dispatch to flows yet. Phase 3. |
| `flow_handoff` | No runtime executor | Saves `target_flow_id` in config, but no backend logic transfers execution to the target flow. Phase 3. |
| `ApiCredential.creator()` | Tenant-scoped User relationship | The User model has a global tenant scope. If credentials are loaded with `->with('creator')` in a cross-tenant admin context, the relationship may return null. Not currently an issue since `creator()` is not eager-loaded anywhere, but worth noting for future admin tooling. |

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

---

## 1. Commit History

### Phase 2 Core Commits (newest first)

| Commit | Description |
|--------|-------------|
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

---

## 2. Architecture Overview

### Tech Stack
- **Backend**: Laravel (PHP), PostgreSQL with UUID primary keys, Row Level Security (RLS)
- **Frontend**: Vanilla JavaScript canvas engine (no React/Vue), Bootstrap 5
- **Auth**: Session-based via `customer.auth` middleware, tenant isolation via `session('customer_tenant_id')`

### File Map

```
app/
  Models/
    Flow.php                    # Flow model (has nodes, connections)
    FlowNode.php                # Node model with type category map
    FlowConnection.php          # Connection model (source → target)
    ApiCredential.php           # Credentials vault model (encrypted)
  Http/Controllers/
    FlowBuilderController.php   # Flow CRUD + canvas save/load
    ApiCredentialController.php  # Credential CRUD with audit logging

database/migrations/
    2026_03_11_000010_create_flows_table.php
    2026_03_11_000011_create_flow_nodes_table.php
    2026_03_11_000012_create_flow_connections_table.php
    2026_03_13_100010_create_api_credentials_table.php

resources/views/quicksms/flows/
    index.blade.php             # Flow list page
    builder.blade.php           # Visual canvas + palette + modals

public/
    css/flow-builder.css        # All flow builder styles
    js/flow-builder.js          # Canvas engine (~3060 lines)

routes/web.php                  # Flow + credential routes
```

### Data Flow

```
Browser Canvas                    Laravel Controller              PostgreSQL
─────────────                    ──────────────────              ──────────
User drags nodes,     ──POST──>  FlowBuilderController::save()   ──TX──>  flows
draws connections,               validates all 21 types via              flow_nodes
configures properties            'in:' whitelist, atomic                 flow_connections
                                 delete+recreate in DB::transaction

Credential modal      ──POST──>  ApiCredentialController::store() ──TX──>  api_credentials
saves via AJAX                   validates auth_type, encrypts            (encrypted:array)
                                 credentials, audit logs
```

---

## 3. Database Schema

### `flows` table
```sql
id              uuid PRIMARY KEY (auto-generated)
account_id      uuid NOT NULL → accounts(id)
name            varchar(255)
description     text NULLABLE
status          varchar(20) DEFAULT 'draft'  -- draft|active|paused|archived
version         integer DEFAULT 1
canvas_meta     jsonb NULLABLE  -- zoom, pan position
created_by      uuid → users(id)
last_activated_at timestamptz NULLABLE
created_at      timestamptz
updated_at      timestamptz
deleted_at      timestamptz NULLABLE
```
RLS: `account_id::text = current_setting('app.current_tenant_id', true)`

### `flow_nodes` table
```sql
id              uuid PRIMARY KEY (auto-generated)
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
```

### `flow_connections` table
```sql
id              uuid PRIMARY KEY (auto-generated)
flow_id         uuid NOT NULL → flows(id) ON DELETE CASCADE
source_node_uid varchar(64) NOT NULL
target_node_uid varchar(64) NOT NULL
source_handle   varchar(50) DEFAULT 'default'  -- default|yes|no|success|error
label           varchar(255) NULLABLE
created_at      timestamptz
updated_at      timestamptz
```

### `api_credentials` table
```sql
id              uuid PRIMARY KEY (auto-generated)
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
Grants: `portal_rw` (CRUD), `portal_ro` (SELECT), `svc_red` (ALL), `ops_admin` (ALL)

---

## 4. Backend (Laravel)

### Routes (`routes/web.php`)

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

### FlowBuilderController

**`builder($id = null)`** — Loads the canvas view with all dynamic data:
- `$sender_ids` — approved sender IDs for the account
- `$rcs_agents` — RCS agents usable by the user
- `$contactLists` — contact lists for the account
- `$tags` — tags for the account
- `$optOutLists` — opt-out lists for the account
- `$activeFlows` — other flows (for flow handoff dropdown)
- `$apiCredentials` — credentials vault (id, name, auth_type only)

All passed to `builder.blade.php` as `window.__flowBuilderData`.

**`save(Request $request, $id)`** — Atomic save of entire flow canvas:
1. Validates all nodes against the 21-type whitelist: `'nodes.*.type' => 'required|in:trigger_api,trigger_webhook,...,end'`
2. Validates connections reference existing `node_uid` values
3. Wraps in `DB::transaction()`:
   - Deletes all existing nodes and connections
   - Re-creates them from the request payload
4. Returns success JSON

**`load($id)`** — Returns flow data as JSON for canvas hydration.

**`store()`** — Creates a new flow with `account_id` and `created_by` from session.

**`duplicate($id)`** — Deep-copies flow including all nodes and connections.

### ApiCredentialController

**`index()`** — Returns metadata only (id, name, auth_type, description, last_used_at, created_at). Never returns the `credentials` column (it's in `$hidden`).

**`store()`** — Validates `auth_type` against `in:none,basic,bearer,api_key,custom_header`. Sets `account_id` from `session('customer_tenant_id')` and `created_by` from `session('customer_user_id')`. Records `AccountAuditLog` entry.

**`update()`** — Scoped to tenant via `forAccount()`. Records diff of changed fields (credentials redacted). Records `AccountAuditLog` entry.

**`destroy()`** — Soft deletes. Records `AccountAuditLog` entry.

### Models

**`Flow`** — `$fillable`: account_id, created_by, name, description, status, version, canvas_meta, last_activated_at. Relationships: `nodes()`, `connections()`, `creator()`.

**`FlowNode`** — `$fillable`: flow_id, node_uid, type, label, config, position_x, position_y. `config` cast to array. `getCategory()` maps all 21 types to trigger/action/logic/end.

**`FlowConnection`** — `$fillable`: flow_id, source_node_uid, target_node_uid, source_handle, label.

**`ApiCredential`** — `$fillable`: account_id, created_by, name, auth_type, credentials, description, last_used_at. `credentials` cast to `encrypted:array`. `$hidden = ['credentials']`.

---

## 5. Frontend (Vanilla JS Canvas Engine)

### Node Type Registry (21 types)

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
| `decision_webhook` | Webhook Decision | fa-code-branch | **['yes', 'no']** | Yes — URL, method, credential, condition_type, json_path, compare_value |

#### End (3) — `inputs: true`, `outputs: []`

| Type | Label | Icon | Config |
|------|-------|------|--------|
| `inbox_handoff` | Inbox Handoff | fa-headset | assign_to, priority, note |
| `flow_handoff` | Flow Handoff | fa-exchange-alt | target_flow_id (dynamic: activeFlows), pass_context (checkbox) |
| `end` | End Flow | fa-stop-circle | none |

### Custom Property Renderers

**`_renderWebhookProperties(node)`** (line 2603):
- URL input, method select (GET/POST/PUT/DELETE)
- Dynamic headers (key-value pairs, add/remove)
- Request body textarea (shown for POST/PUT)
- Credential selector dropdown (populated from `__flowBuilderData.apiCredentials`)
- "Create New Credential" option opens the credential modal
- Timeout input

**`_renderActionGroupProperties(node)`** (line 2486):
- Ordered list of steps stored in `node.config.steps[]`
- Each step: type selector + type-specific fields
- 8 step types defined in `ACTION_GROUP_STEP_TYPES`:
  - `add_tag` / `remove_tag` — tag_name field
  - `add_to_list` / `remove_from_list` — list_id (dynamic: contactLists)
  - `add_optout` / `remove_optout` — opt_out_list_id (dynamic: optOutLists)
  - `update_contact` — field_name + field_value
  - `wait` — duration value + unit (minutes/hours/days)
- "Add Step" button appends new steps
- Step removal via X button

**`_renderDecisionContactProperties(node)`** (line 2716):
- Condition type select: in_list, not_in_list, has_tag, not_has_tag, is_opted_out, not_opted_out, field_equals, field_contains
- Conditional fields toggle with `classList.add/remove('d-none')`:
  - List selector for in_list/not_in_list (dynamic: contactLists)
  - Tag selector for has_tag/not_has_tag (dynamic: tags)
  - Opt-out list selector for is_opted_out/not_opted_out (dynamic: optOutLists)
  - Field name + compare value for field_equals/field_contains

**`_renderDecisionWebhookProperties(node)`** (line 2802):
- URL input, method select (GET/POST)
- Credential selector from apiCredentials
- Condition type: status_code, json_path_equals, json_path_contains, json_path_exists, response_empty
- JSON path input (shown when condition starts with "json_path")
- Compare value input (hidden for json_path_exists, response_empty)

### Dual Output Ports (Webhook Node)

The webhook node renders two output ports instead of one:
- **Success port** (`.port-output-success`): positioned at `left:30%`, green border (`#2e7d32`)
- **Error port** (`.port-output-error`): positioned at `left:70%`, red border (`#c62828`)
- Branch labels: "success" (green background) and "error" (red background)
- Connections from these ports use `source_handle: 'success'` or `source_handle: 'error'`

Decision nodes similarly have `yes`/`no` dual output ports.

### Credential Modal

- Modal ID: `#flowCredentialModal`
- Auth type selector toggles field visibility using `classList.add/remove('d-none')`:
  - `basic` → username + password inputs
  - `bearer` → token input
  - `api_key` → header name (default "X-API-Key") + key input
  - `custom_header` → header name + header value input
- CSRF token read from `meta[name="csrf-token"]` — if missing, shows "Session expired" error
- On save success: adds credential to `__flowBuilderData.apiCredentials`, updates dropdown, closes modal
- Error display: `#credentialError` element with `d-none` class toggle

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

### Templates (3 built-in)

| Template | Nodes | Key Features |
|----------|-------|--------------|
| `welcome` | API Trigger → Send Message → Wait → Send Message → Tag Action → End | Basic onboarding sequence |
| `reminder` | API Trigger → Wait → Send Message → Decision → Tag/Fallback → End | Decision branching (yes/no) |
| `delivery` | API Trigger → Send Message → Decision → Webhook/Handoff → End | Webhook dual-port + inbox handoff |

### Canvas Engine Key Methods

| Method | Line | Purpose |
|--------|------|---------|
| `_addNode(type, x, y)` | ~380 | Creates node from type definition |
| `_renderNode(nodeId, node)` | ~440 | Renders node card with ports |
| `_showProperties(nodeId)` | ~650 | Routes to correct property renderer |
| `_getConfigPreview(type, config)` | ~780 | Returns canvas card preview text |
| `_saveFlow()` | ~1600 | Serializes and POSTs to `/flows/{id}/save` |
| `_loadFlow(data)` | ~1680 | Hydrates canvas from server JSON |
| `_drawConnections()` | ~1100 | SVG bezier curves between ports |
| `_refreshNode(nodeId)` | ~842 | Updates node card after config change |
| `_openCredentialModal(nodeId)` | ~2940 | Opens credential modal |
| `_saveCredential(nodeId)` | ~2975 | AJAX POST to create credential |

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

### Credential Encryption
- `credentials` column uses `'encrypted:array'` cast — encrypted at rest via Laravel's `Crypt` facade
- `$hidden = ['credentials']` — never serialized in JSON responses
- `index()` endpoint returns only metadata: id, name, auth_type, description, last_used_at, created_at

### Permission Gating
- Credential write routes (POST/PUT/DELETE) require `permission:manage_api_credentials`
- Credential read (GET) open to all authenticated users (needed for dropdown population in webhook nodes)
- Flow routes use `customer.auth` middleware for all operations

### Audit Trail
- All credential CRUD operations record `AccountAuditLog` entries
- Audit records include: actor (user_id, user_name), action type, credential details
- Credential values are redacted: `['from' => '[REDACTED]', 'to' => '[REDACTED]']`
- Audit logging wrapped in try-catch to not break the primary operation

### CSRF Protection
- All fetch calls include `X-CSRF-TOKEN` header from `meta[name="csrf-token"]`
- Missing CSRF meta tag shows "Session expired. Please refresh the page." instead of silent failure

### Input Validation
- Node types validated against explicit whitelist of 21 types (no arbitrary strings)
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
5. **Scope all queries to tenant** using `session('customer_tenant_id')`
6. **Record `AccountAuditLog`** for any new CRUD operations on sensitive resources
7. **Wrap audit logging in try-catch** — audit failures must not break the primary operation
8. **Read CSRF token from `meta[name="csrf-token"]`** and check for presence before fetch calls
9. **Use `permission:` middleware** on write routes for sensitive resources
10. **Keep `credentials` in `$hidden`** and never return raw credential values in API responses

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

### When Adding New Node Types

1. Add the type definition to `NODE_TYPES` in `flow-builder.js` with correct category, outputs, inputs, and configFields
2. Add the type to the `getCategory()` map in `FlowNode.php`
3. Add the type to the `'nodes.*.type' => 'required|in:...'` validation rule in `FlowBuilderController::save()`
4. Add the type to the palette sidebar in `builder.blade.php` in the correct section
5. If the node has `customProperties: true`, add a `_render{Type}Properties()` method
6. Add a `_getConfigPreview()` case for the new type
7. All three locations (JS, PHP model, PHP validation) must stay in sync

### When Adding New Dynamic Data

1. Query in `FlowBuilderController::builder()`, scoped to `$accountId`
2. Pass to the Blade view
3. Add to `window.__flowBuilderData` in the Blade template
4. Reference via `dynamic: 'keyName'` in config field definitions

---

## 8. Verification Checklist

### Backend
- [ ] `php -l` passes on all PHP files
- [ ] All 21 types present in FlowBuilderController validation rule
- [ ] All 21 types present in FlowNode::getCategory() map
- [ ] ApiCredential `$fillable` includes `account_id` and `created_by`
- [ ] ApiCredential `$hidden` includes `credentials`
- [ ] Credential routes have `permission:manage_api_credentials` on POST/PUT/DELETE
- [ ] Credential store/update/destroy record `AccountAuditLog` entries
- [ ] All flow queries scoped to `session('customer_tenant_id')`
- [ ] Migration includes RLS policy, UUID trigger, and grants

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

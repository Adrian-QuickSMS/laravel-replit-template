# Flow Builder Phase 2 — Implementation Prompt

## Overview

Expand the QuickSMS Flow Builder from 8 node types to 21 node types, add an API Credentials vault, Action Group ("Quick Steps") node, webhook success/error dual-port branching, Contact Decision and Webhook Decision nodes, and dynamic data loading from the server.

This is a Laravel application using PostgreSQL with UUID primary keys, Row Level Security (RLS), and Bootstrap 5. The flow builder is a vanilla JavaScript canvas engine (no React/Vue).

---

## IMPORTANT RULES

Follow these project conventions strictly:

1. **UUIDs everywhere** — all new tables use `uuid('id')->primary()` with a `gen_random_uuid()` trigger, RLS policies, and grants for `portal_rw`, `portal_ro`, `svc_red`, `ops_admin`
2. **Modal instantiation** — ALWAYS use `bootstrap.Modal.getOrCreateInstance(modalEl)`, NEVER `new bootstrap.Modal()`
3. **Visibility toggling** — ALWAYS use `classList.add('d-none')` / `classList.remove('d-none')`, NEVER `style.display`
4. **Mass assignment** — NEVER put tenant identifiers (`account_id`, `created_by`) in `$fillable`. Set them explicitly in the controller
5. **Node type validation** — the `save()` endpoint MUST whitelist all 21 node types using Laravel's `in:` validation rule
6. **CSRF** — read token from `meta[name="csrf-token"]` and send as `X-CSRF-TOKEN` header in fetch calls
7. **Credentials security** — the `credentials` column uses `encrypted:array` cast, is in `$hidden`, and the API index endpoint only returns metadata (id, name, auth_type), NEVER secrets

---

## Files to Create

### 1. `database/migrations/2026_03_13_100010_create_api_credentials_table.php`

Create the `api_credentials` table with this exact structure:

```php
Schema::create('api_credentials', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('account_id');
    $table->string('name', 100);
    $table->string('auth_type', 20)->default('bearer');
    $table->text('credentials'); // Encrypted JSON via Laravel Crypt
    $table->text('description')->nullable();
    $table->timestampTz('last_used_at')->nullable();
    $table->uuid('created_by');
    $table->timestampsTz();
    $table->softDeletes();
    $table->foreign('account_id')->references('id')->on('accounts');
    $table->foreign('created_by')->references('id')->on('users');
    $table->index('account_id');
});
```

Then add (using `DB::unprepared`):
- UUID auto-generation trigger (`generate_uuid_api_credentials`)
- RLS policy: `account_id::text = current_setting('app.current_tenant_id', true)`
- Grants: `GRANT SELECT, INSERT, UPDATE, DELETE ON api_credentials TO portal_rw`, `GRANT SELECT ON api_credentials TO portal_ro`, `GRANT ALL ON api_credentials TO svc_red`, `GRANT ALL ON api_credentials TO ops_admin`

The `down()` method must drop the policy, disable RLS, drop the trigger, drop the function, then drop the table.

### 2. `app/Models/ApiCredential.php`

```php
class ApiCredential extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'auth_type',
        'credentials',
        'description',
        'last_used_at',
    ];
    // NOTE: account_id and created_by are NOT in $fillable — set explicitly in controller

    protected $casts = [
        'credentials' => 'encrypted:array',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['credentials'];

    public function scopeForAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

### 3. `app/Http/Controllers/ApiCredentialController.php`

CRUD controller with 4 methods:
- `index()` — returns JSON list of credentials for the tenant (metadata only: id, name, auth_type, description, last_used_at, created_at). Uses `session('customer_tenant_id')`.
- `store(Request $request)` — validates name, auth_type (`in:none,basic,bearer,api_key,custom_header`), credentials (required array), description. Creates with explicit `account_id` from session and `created_by` from session. Returns the new credential's id, name, auth_type.
- `update(Request $request, $id)` — scoped to tenant, updates only provided fields.
- `destroy($id)` — scoped to tenant, soft deletes.

---

## Files to Modify

### 4. `routes/web.php`

Add after the existing flows route group (around line 135):

```php
// API Credentials vault (for webhook/API nodes in Flow Builder)
Route::middleware('customer.auth')->prefix('api-credentials')->controller(\App\Http\Controllers\ApiCredentialController::class)->group(function () {
    Route::get('/', 'index')->name('api-credentials.index');
    Route::post('/', 'store')->name('api-credentials.store');
    Route::put('/{id}', 'update')->name('api-credentials.update');
    Route::delete('/{id}', 'destroy')->name('api-credentials.destroy');
});
```

### 5. `app/Http/Controllers/FlowBuilderController.php`

**In the `builder()` method**, add these data queries (all scoped to `$accountId = session('customer_tenant_id')`):

```php
$contactLists = \App\Models\ContactList::where('account_id', $accountId)
    ->orderBy('name')->get()->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->toArray();

$tags = \App\Models\Tag::where('account_id', $accountId)
    ->orderBy('name')->get()->map(fn($t) => ['id' => $t->id, 'name' => $t->name])->toArray();

$optOutLists = \App\Models\OptOutList::where('account_id', $accountId)
    ->orderBy('name')->get()->map(fn($o) => ['id' => $o->id, 'name' => $o->name])->toArray();

$activeFlows = Flow::where('account_id', $accountId)
    ->where('id', '!=', $id ?? 0)
    ->orderBy('name')->get()->map(fn($f) => ['id' => $f->id, 'name' => $f->name])->toArray();

$apiCredentials = [];
if (class_exists(\App\Models\ApiCredential::class)) {
    $apiCredentials = \App\Models\ApiCredential::where('account_id', $accountId)
        ->orderBy('name')->get()
        ->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'auth_type' => $c->auth_type])->toArray();
}
```

Pass all of these to the view: `contact_lists`, `tags`, `opt_out_lists`, `active_flows`, `api_credentials`.

**In the `save()` method**, replace the old `$allowedNodeTypes` array with an inline `in:` validation:

```php
'nodes.*.type' => 'required|in:trigger_api,trigger_webhook,trigger_sms_inbound,trigger_rcs_inbound,trigger_campaign,trigger_contact_event,trigger_schedule,send_message,contact,tag_action,list_action,optout_action,webhook,action_group,wait,decision,decision_contact,decision_webhook,inbox_handoff,flow_handoff,end',
```

### 6. `app/Models/FlowNode.php`

Replace the `getCategory()` type map with:

```php
$map = [
    'trigger_api' => 'trigger',
    'trigger_webhook' => 'trigger',
    'trigger_sms_inbound' => 'trigger',
    'trigger_rcs_inbound' => 'trigger',
    'trigger_campaign' => 'trigger',
    'trigger_contact_event' => 'trigger',
    'trigger_schedule' => 'trigger',
    'send_message' => 'action',
    'contact' => 'action',
    'tag_action' => 'action',
    'list_action' => 'action',
    'optout_action' => 'action',
    'webhook' => 'action',
    'action_group' => 'action',
    'wait' => 'logic',
    'decision' => 'logic',
    'decision_contact' => 'logic',
    'decision_webhook' => 'logic',
    'inbox_handoff' => 'end',
    'flow_handoff' => 'end',
    'end' => 'end',
];
return $map[$this->type] ?? 'action';
```

### 7. `resources/views/quicksms/flows/builder.blade.php`

**Palette sidebar** — Replace the existing palette nodes with all 21 node types organized in 4 sections:

**Triggers section** (7 nodes):
| data-type | Icon class | Name | Description |
|-----------|-----------|------|-------------|
| trigger_api | fa-plug | API Trigger | Start via API call |
| trigger_webhook | fa-satellite-dish | External Webhook | Receive webhook POST |
| trigger_sms_inbound | fa-comment-dots | SMS Inbound | Trigger on inbound SMS |
| trigger_rcs_inbound | fa-hand-pointer | RCS Inbound | RCS reply or button tap |
| trigger_campaign | fa-bullhorn | Campaign Event | Campaign activity trigger |
| trigger_contact_event | fa-address-book | Contact Event | Contact book changes |
| trigger_schedule | fa-clock | Schedule | Time-based trigger |

**Actions section** (7 nodes):
| data-type | Icon class | Name | Description |
|-----------|-----------|------|-------------|
| send_message | fa-paper-plane | Send Message | SMS or RCS message |
| contact | fa-user-plus | Contact | Create, update or delete |
| tag_action | fa-tag | Tag | Add or remove tags |
| list_action | fa-list | List | Add or remove from list |
| optout_action | fa-ban | Opt-Out | Manage opt-out status |
| webhook | fa-globe | Webhook | Call external API |
| action_group | fa-layer-group | Quick Steps | Multiple actions in one |

**Logic section** (4 nodes):
| data-type | Icon class | Name | Description |
|-----------|-----------|------|-------------|
| wait | fa-hourglass-half | Wait / Delay | Pause execution |
| decision | fa-random | Decision | If/else branching |
| decision_contact | fa-address-card | Contact Decision | Branch on contact data |
| decision_webhook | fa-code-branch | Webhook Decision | Branch on API response |

**End section** (3 nodes):
| data-type | Icon class | Name | Description |
|-----------|-----------|------|-------------|
| inbox_handoff | fa-headset | Inbox Handoff | Transfer to agent |
| flow_handoff | fa-exchange-alt | Flow Handoff | Continue in another flow |
| end | fa-stop-circle | End Flow | Terminate flow |

Each palette node follows this HTML pattern:
```html
<div class="palette-node" draggable="true" data-type="TYPE_KEY">
    <div class="palette-node-icon CATEGORY"><i class="fas ICON_CLASS"></i></div>
    <div class="palette-node-info">
        <div class="palette-node-name">LABEL</div>
        <div class="palette-node-desc">DESCRIPTION</div>
    </div>
</div>
```

**`window.__flowBuilderData`** — expand to include:
```javascript
window.__flowBuilderData = {
    senderIds: @json($sender_ids),
    rcsAgents: @json($rcs_agents),
    contactLists: @json($contact_lists ?? []),
    tags: @json($tags ?? []),
    optOutLists: @json($opt_out_lists ?? []),
    activeFlows: @json($active_flows ?? []),
    apiCredentials: @json($api_credentials ?? [])
};
```

**Credential modal** — add a Bootstrap modal with id `flowCredentialModal`:
- Fields: Name (text), Auth Type (select: none/basic/bearer/api_key/custom_header)
- Conditional credential fields per auth type:
  - `basic` → Username + Password
  - `bearer` → Token
  - `api_key` → Header Name (default "X-API-Key") + API Key
  - `custom_header` → Header Name + Header Value
- Password-type inputs for all secrets
- Save button with id `flowCredentialSaveBtn`
- Error display area with id `credentialError` (starts with `d-none`)
- Toggle credential field visibility using `classList.add/remove('d-none')`, NOT `style.display`

### 8. `public/css/flow-builder.css`

Add these new style blocks (append before the responsive media query):

**Action Group step cards:**
```css
.action-group-step { background: #f8f8f8; border: 1px solid #eee; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; position: relative; }
.action-group-step:hover { border-color: #d0d0d0; }
.step-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
.step-number { width: 22px; height: 22px; border-radius: 50%; background: #886CC0; color: #fff; font-size: 0.68rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.step-drag-handle { cursor: grab; color: #ccc; font-size: 0.9rem; padding: 0 2px; }
.step-drag-handle:hover { color: #886CC0; }
.step-remove { margin-left: auto; background: none; border: none; color: #ccc; cursor: pointer; padding: 2px 6px; font-size: 0.75rem; }
.step-remove:hover { color: #c62828; }
.step-fields { padding-left: 30px; }
.step-fields .form-control, .step-fields .form-select { font-size: 0.78rem; margin-bottom: 6px; }
```

**Webhook success/error ports:**
```css
.node-port.port-output-success { bottom: -7px; left: 30%; margin-left: -7px; border-color: #2e7d32; }
.node-port.port-output-success:hover { background: #2e7d32; border-color: #2e7d32; }
.node-port.port-output-error { bottom: -7px; left: 70%; margin-left: -7px; border-color: #c62828; }
.node-port.port-output-error:hover { background: #c62828; border-color: #c62828; }
.branch-label.success { left: 20%; background: #e8f5e9; color: #2e7d32; }
.branch-label.error { left: 60%; background: #ffebee; color: #c62828; }
```

**Credential fields:**
```css
.credential-fields { transition: opacity 0.15s; }
```

### 9. `public/js/flow-builder.js`

This is the largest change. Here is the complete specification:

#### 9a. NODE_TYPES object — replace entirely with all 21 types

The complete NODE_TYPES definition with all config fields, outputs, and properties. Here are the key structural details:

**Triggers (7 types)** — all have `inputs: false`, `outputs: ['default']`:
- `trigger_api` — configFields: endpoint_note (info), variables (textarea)
- `trigger_webhook` — configFields: webhook_url_note (info), payload_schema (textarea), auth_method (select: none/hmac), hmac_secret (text, showWhen auth_method=hmac)
- `trigger_sms_inbound` — configFields: sender_id (select, dynamic: senderIds), match_type (select: any/keyword/contains/regex), keywords (text, showWhen match_type=keyword|contains)
- `trigger_rcs_inbound` — configFields: rcs_agent_id (select, dynamic: rcsAgents), match_type (select: any/postback/text), postback_data (text, showWhen match_type=postback), text_match (text, showWhen match_type=text)
- `trigger_campaign` — configFields: campaign_event (select: campaign_completed/message_delivered/message_failed/link_clicked/reply_received), campaign_id (text)
- `trigger_contact_event` — configFields: event_type (select: contact_created/contact_updated/added_to_list/removed_from_list/tag_added/tag_removed/opted_out/opted_in), filter_list_id (select, dynamic: contactLists, showWhen event_type=added_to_list|removed_from_list), filter_tag_id (select, dynamic: tags, showWhen event_type=tag_added|tag_removed)
- `trigger_schedule` — configFields: schedule_type (select: once/daily/weekly/monthly), time (text), date (text)

**Actions (7 types)** — all have `inputs: true`:
- `send_message` — `customProperties: true`, `dynamicOutputs: true`, outputs: ['default'], configFields: []
- `contact` — outputs: ['default'], configFields: action (select: create/update/delete), phone_number, first_name, last_name, email (all text)
- `tag_action` — outputs: ['default'], configFields: action (select: add/remove), tag_name (text)
- `list_action` — outputs: ['default'], configFields: action (select: add/remove), list_id (select, dynamic: contactLists)
- `optout_action` — outputs: ['default'], configFields: action (select: add/remove), opt_out_list_id (select, dynamic: optOutLists), reason (text optional)
- `webhook` — `outputs: ['success', 'error']`, `customProperties: true`, configFields: []
- `action_group` — outputs: ['default'], `customProperties: true`, configFields: []

**Logic (4 types)** — all have `inputs: true`:
- `wait` — outputs: ['default'], configFields: wait_type (select: duration/until_date/until_event), duration_value (text), duration_unit (select: minutes/hours/days), quiet_hours (checkbox)
- `decision` — outputs: ['yes', 'no'], configFields: condition_type (select), field (text), operator (select), compare_value (text), timeout (text)
- `decision_contact` — outputs: ['yes', 'no'], `customProperties: true`, configFields: []
- `decision_webhook` — outputs: ['yes', 'no'], `customProperties: true`, configFields: []

**End (3 types)** — all have `outputs: []`, `inputs: true`:
- `inbox_handoff` — configFields: assign_to (select), priority (select), note (textarea)
- `flow_handoff` — configFields: target_flow_id (select, dynamic: activeFlows), pass_context (checkbox)
- `end` — configFields: []

#### 9b. TEMPLATES — update node type references

In the three templates (welcome, reminder, delivery), change:
- `tag` → `tag_action`
- Remove any references to `send_sms`, `send_rcs`, `trigger_sms_keyword`, `trigger_rcs_button`

#### 9c. Dynamic select population

When rendering config fields in `_showProperties`, if a field has `dynamic` property, populate its options from `window.__flowBuilderData[field.dynamic]`. Each item has `id` and `name`.

#### 9d. Conditional field visibility (`showWhen`)

When a field has `showWhen: { key: 'other_field_key', values: ['val1', 'val2'] }`:
- Wrap it in a `<div class="config-field-wrap" data-field-key="FIELD_KEY">`
- On initial render, set `d-none` class if the trigger field's current value is not in the values array
- Add a change listener on the trigger field to toggle `d-none` class

#### 9e. Config preview on canvas (`_getConfigPreview`)

Add preview cases for all new node types. The preview shows a short summary on the canvas card:
- `trigger_webhook` → "Webhook endpoint"
- `trigger_sms_inbound` → show match_type & keywords
- `trigger_rcs_inbound` → show match_type
- `trigger_campaign` → show campaign_event
- `trigger_contact_event` → show event_type
- `contact` → show action (create/update/delete)
- `tag_action` → show "Add/Remove: tag_name"
- `list_action` → show "Add/Remove from list"
- `optout_action` → show "Add/Remove opt-out"
- `action_group` → show "N steps configured" based on config.steps array length
- `decision_contact` → show condition_type
- `decision_webhook` → show URL
- `flow_handoff` → show target flow name

#### 9f. Dual output ports (success/error)

For nodes with `outputs: ['success', 'error']` (the webhook node):
- Render two output ports: `.port-output-success` at left:30% and `.port-output-error` at left:70%
- Add branch labels: "success" (green) and "error" (red)
- Port positions must be calculated correctly for connection drawing

#### 9g. Custom property renderers

For nodes with `customProperties: true` that are NOT `send_message`, add custom property panel renderers:

**`_renderWebhookProperties(node, nodeId)`:**
- URL input (text)
- Method select (GET/POST/PUT/DELETE)
- Headers section (key-value pairs with add/remove)
- Body textarea (for POST/PUT)
- Credential selector dropdown (populated from `__flowBuilderData.apiCredentials`) with a "Create New" option that opens the credential modal
- Timeout input

**`_renderActionGroupProperties(node, nodeId)`:**
- Shows a list of "steps" that can be added/removed/reordered
- Each step has a type selector and type-specific fields
- Step types (defined in `ACTION_GROUP_STEP_TYPES` constant):
  - `add_tag` — fields: tag_name (text)
  - `remove_tag` — fields: tag_name (text)
  - `add_to_list` — fields: list_id (select, dynamic: contactLists)
  - `remove_from_list` — fields: list_id (select, dynamic: contactLists)
  - `add_optout` — fields: opt_out_list_id (select, dynamic: optOutLists)
  - `remove_optout` — fields: opt_out_list_id (select, dynamic: optOutLists)
  - `update_contact` — fields: field_name (text), field_value (text)
  - `wait` — fields: value (text for duration), unit (select: minutes/hours/days)
- "Add Step" button at the bottom
- Steps are stored in `node.config.steps` as an array

**`_renderDecisionContactProperties(node, nodeId)`:**
- Condition type select: in_list, not_in_list, has_tag, not_has_tag, is_opted_out, not_opted_out, field_equals, field_contains
- Conditional fields that show/hide based on condition type:
  - List selector (for in_list/not_in_list) — dynamic: contactLists
  - Tag selector (for has_tag/not_has_tag) — dynamic: tags
  - Opt-out list selector (for is_opted_out/not_opted_out) — dynamic: optOutLists
  - Field name + compare value (for field_equals/field_contains)
- Use `classList.add/remove('d-none')` for toggling, NOT `style.display`

**`_renderDecisionWebhookProperties(node, nodeId)`:**
- URL input
- Method select (GET/POST)
- Credential selector dropdown (from apiCredentials data)
- Condition type select: status_code, json_path_equals, json_path_contains, json_path_exists, response_empty
- JSON path input (shown when condition type starts with "json_path")
- Compare value input (hidden for json_path_exists and response_empty)
- Use `classList.add/remove('d-none')` for toggling

#### 9h. Credential modal JavaScript

**`_openCredentialModal(nodeId)`:**
- Opens the `#flowCredentialModal` using `bootstrap.Modal.getOrCreateInstance()`
- Stores the nodeId so `_saveCredential` knows which node to update

**`_toggleCredentialFields(authType)`:**
- Hides all `.credential-fields` elements (add `d-none`)
- Shows the matching `#credentialFields-{authType}` (remove `d-none`)

**`_saveCredential(nodeId)`:**
- Reads form fields, builds credentials object based on auth_type
- POSTs to `/api-credentials` with CSRF token from `meta[name="csrf-token"]`
- On success: adds new credential to `__flowBuilderData.apiCredentials`, updates the credential dropdown in the node properties, closes modal
- On error: shows error in `#credentialError`

#### 9i. `_showProperties` routing

Update the properties panel display to route `customProperties` nodes to their custom renderers:
- `webhook` → `_renderWebhookProperties`
- `action_group` → `_renderActionGroupProperties`
- `decision_contact` → `_renderDecisionContactProperties`
- `decision_webhook` → `_renderDecisionWebhookProperties`
- `send_message` → already has custom handling (keep existing)

#### 9j. Properties panel visibility

When showing/hiding the properties panel:
- Show: `panel.classList.remove('d-none'); panel.classList.add('d-flex');`
- Hide: `panel.classList.add('d-none'); panel.classList.remove('d-flex');`

---

## Verification Checklist

After implementing, verify:

1. All 21 node types appear in the palette sidebar, organized in 4 sections (Triggers, Actions, Logic, End)
2. Each node can be dragged onto the canvas and displays the correct icon, label, and category color
3. Clicking each node opens its properties panel with the correct config fields
4. Dynamic dropdowns (sender IDs, RCS agents, contact lists, tags, opt-out lists, active flows, API credentials) populate from `__flowBuilderData`
5. `showWhen` conditional fields toggle visibility correctly
6. Webhook node shows two output ports (success in green, error in red) with branch labels
7. Action Group ("Quick Steps") node allows adding, removing, and configuring multiple steps
8. Decision Contact node shows/hides conditional fields based on condition type
9. Decision Webhook node shows/hides JSON path and compare value fields
10. Credential modal opens, toggles field sets by auth type, and saves via AJAX
11. Save/load flow round-trips correctly with all new node types
12. No `new bootstrap.Modal()` anywhere — only `getOrCreateInstance()`
13. No `style.display` anywhere — only `classList` with `d-none`/`d-flex`
14. The `api_credentials` migration runs cleanly with UUID, RLS, and grants
15. PHP syntax check passes on all modified files (`php -l`)

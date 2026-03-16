# Flow Builder Phase 3 — Implementation Specification

> **Purpose**: Complete specification for Phase 3 of the Flow Builder.
> Phase 2 built the visual canvas (schema designer). Phase 3 brings it to life
> with a runtime execution engine, audience trigger, live monitoring, variable
> system, A/B testing, and analytics.
>
> **Branch**: `claude/quicksms-flow-builder-XtahH`
> **Depends on**: Phase 2 (all 6 commits through `6697542`)

---

## Table of Contents

1. [Scope Overview](#1-scope-overview)
2. [Database Schema (New Tables)](#2-database-schema-new-tables)
3. [Flow Execution Engine](#3-flow-execution-engine)
4. [Audience Trigger Node](#4-audience-trigger-node)
5. [Variable System](#5-variable-system)
6. [A/B Split Node](#6-ab-split-node)
7. [Live Flow Monitor](#7-live-flow-monitor)
8. [Flow Analytics Dashboard](#8-flow-analytics-dashboard)
9. [Runtime Node Handlers](#9-runtime-node-handlers)
10. [Frontend Changes](#10-frontend-changes)
11. [Routes & Controllers](#11-routes--controllers)
12. [Anti-Drift Rules (Phase 3 Additions)](#12-anti-drift-rules-phase-3-additions)
13. [Implementation Order](#13-implementation-order)

---

## 1. Scope Overview

### What Phase 3 Delivers

| Component | What It Does |
|-----------|-------------|
| **Execution Engine** | Traverses the flow graph, executes each node, manages contact state and branching |
| **Audience Trigger** | New trigger node — select contacts from contact book, upload file, paste numbers (reuses campaign iframe) |
| **Variable System** | Pass data between nodes — `{{contact.first_name}}`, `{{webhook.response.tracking_url}}`, template interpolation |
| **A/B Split Node** | New logic node — split contacts into branches by ratio, measure which performs better |
| **Live Monitor** | Real-time overlay on the canvas — node count badges, contact status, click-to-inspect |
| **Analytics Dashboard** | Conversion funnel, drop-off analysis, message performance per node, cost tracking |

### What Already Exists (Phase 2 → Phase 3 Bridges)

| Phase 2 Asset | How Phase 3 Uses It |
|---------------|---------------------|
| `send_message` node config (channel, content, sender, RCS payload) | Execution engine reads config, calls `DeliveryService` |
| `webhook` node config (URL, method, headers, credential) | Execution engine makes HTTP call, stores response in variables |
| `decision` / `decision_contact` / `decision_webhook` configs | Execution engine evaluates condition, follows yes/no branch |
| `wait` node config (duration, quiet_hours) | Execution engine schedules delayed job |
| `action_group` steps | Execution engine runs steps sequentially |
| `DeliveryService` (campaign infrastructure) | Reused for actual message sending — no new gateway code |
| `CampaignService.prepare()` recipient resolution | Reused for audience trigger contact resolution |
| `/messages/send?context=flow` iframe | Reused for audience trigger contact/recipient selection |
| `window.__flowBuilderData` dynamic data injection | Extended with flow run status data for live monitoring |

---

## 2. Database Schema (New Tables)

### `flow_runs` — One row per flow execution

```sql
CREATE TABLE flow_runs (
    id              uuid PRIMARY KEY,
    flow_id         uuid NOT NULL REFERENCES flows(id) ON DELETE CASCADE,
    account_id      uuid NOT NULL REFERENCES accounts(id),
    status          varchar(20) NOT NULL DEFAULT 'pending',
        -- pending | preparing | running | paused | completed | failed | cancelled
    trigger_type    varchar(50) NOT NULL,
        -- Which trigger node started this run
    trigger_config  jsonb,
        -- Snapshot of trigger node config at run start
    audience_summary jsonb,
        -- {total_contacts, total_unique, total_opted_out, total_invalid, sources: [...]}
    variables       jsonb DEFAULT '{}',
        -- Flow-level variables (trigger payload, shared state)
    stats           jsonb DEFAULT '{}',
        -- Running counters: {messages_sent, messages_delivered, messages_failed,
        --   webhooks_called, contacts_completed, contacts_failed, contacts_waiting,
        --   total_cost}
    started_at      timestamptz,
    completed_at    timestamptz,
    created_by      uuid REFERENCES users(id),
    created_at      timestamptz,
    updated_at      timestamptz
);
-- RLS: account_id tenant isolation
-- Indexes: (flow_id, status), (account_id, created_at DESC)
```

### `flow_run_contacts` — One row per contact in a flow run

```sql
CREATE TABLE flow_run_contacts (
    id                  uuid PRIMARY KEY,
    flow_run_id         uuid NOT NULL REFERENCES flow_runs(id) ON DELETE CASCADE,
    contact_id          uuid REFERENCES contacts(id) ON DELETE SET NULL,
    mobile_number       varchar(20) NOT NULL,
    status              varchar(20) NOT NULL DEFAULT 'pending',
        -- pending | active | waiting | completed | failed | cancelled | opted_out
    current_node_uid    varchar(64),
        -- Which node this contact is currently at (null = not started or completed)
    variables           jsonb DEFAULT '{}',
        -- Per-contact variables: trigger data, webhook responses, computed values
    path                jsonb DEFAULT '[]',
        -- Ordered array of {node_uid, entered_at, exited_at, outcome}
        -- Tracks the exact path this contact took through the flow
    ab_assignments      jsonb DEFAULT '{}',
        -- {split_node_uid: 'A' | 'B' | 'C'} — which branch per split node
    error_message       text,
    entered_at          timestamptz,
    completed_at        timestamptz,
    created_at          timestamptz,
    updated_at          timestamptz
);
-- RLS: via flow_runs → account_id
-- Indexes: (flow_run_id, status), (flow_run_id, current_node_uid), (mobile_number)
-- Partial index: (flow_run_id) WHERE status = 'active' OR status = 'waiting'
```

### `flow_run_logs` — Detailed execution log per node execution

```sql
CREATE TABLE flow_run_logs (
    id                  uuid PRIMARY KEY,
    flow_run_id         uuid NOT NULL REFERENCES flow_runs(id) ON DELETE CASCADE,
    flow_run_contact_id uuid REFERENCES flow_run_contacts(id) ON DELETE CASCADE,
    node_uid            varchar(64) NOT NULL,
    node_type           varchar(50) NOT NULL,
    action              varchar(30) NOT NULL,
        -- entered | executed | succeeded | failed | skipped | waiting | resumed
    input_data          jsonb,
        -- What the node received (variables snapshot, resolved template)
    output_data         jsonb,
        -- What the node produced (webhook response, message_id, decision result)
    error_data          jsonb,
        -- Error details if action = 'failed'
    duration_ms         integer,
    created_at          timestamptz DEFAULT NOW()
);
-- RLS: via flow_runs → account_id
-- Indexes: (flow_run_id, node_uid), (flow_run_id, flow_run_contact_id)
-- Partition by created_at (monthly) if volume warrants it
```

### `flow_ab_results` — Aggregated A/B test results per split node

```sql
CREATE TABLE flow_ab_results (
    id              uuid PRIMARY KEY,
    flow_run_id     uuid NOT NULL REFERENCES flow_runs(id) ON DELETE CASCADE,
    node_uid        varchar(64) NOT NULL,
        -- The ab_split node
    branch          varchar(10) NOT NULL,
        -- 'A', 'B', 'C', etc.
    contact_count   integer DEFAULT 0,
    messages_sent   integer DEFAULT 0,
    messages_delivered integer DEFAULT 0,
    replies_received integer DEFAULT 0,
    links_clicked   integer DEFAULT 0,
    conversions     integer DEFAULT 0,
    total_cost      decimal(10,4) DEFAULT 0,
    created_at      timestamptz,
    updated_at      timestamptz,
    UNIQUE(flow_run_id, node_uid, branch)
);
-- RLS: via flow_runs → account_id
```

### Schema for existing tables — additions

```sql
-- flows table: add columns
ALTER TABLE flows ADD COLUMN total_runs integer DEFAULT 0;
ALTER TABLE flows ADD COLUMN last_run_at timestamptz;
```

---

## 3. Flow Execution Engine

### Architecture

```
                    ┌──────────────────────┐
                    │   FlowRunService     │  ← Orchestrator: creates run, resolves audience,
                    │                      │     dispatches first batch of jobs
                    └──────────┬───────────┘
                               │
                    ┌──────────▼───────────┐
                    │  ExecuteFlowNode     │  ← Queue job (one per contact per node)
                    │  (Laravel Job)       │     Executes single node for single contact
                    └──────────┬───────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
    ┌─────────▼──────┐ ┌──────▼───────┐ ┌──────▼───────┐
    │ NodeHandler    │ │ NodeHandler  │ │ NodeHandler  │
    │ (SendMessage)  │ │ (Decision)   │ │ (Webhook)    │  ... etc
    └────────────────┘ └──────────────┘ └──────────────┘
```

### Key Classes

#### `FlowRunService` (`app/Services/Flow/FlowRunService.php`)

Orchestrator. Responsible for:

```php
class FlowRunService
{
    /**
     * Start a new flow run.
     * 1. Validates flow is active
     * 2. Creates FlowRun record
     * 3. Resolves audience (contacts) from trigger config
     * 4. Creates FlowRunContact records
     * 5. Dispatches ExecuteFlowNode jobs for the trigger's output connections
     */
    public function startRun(Flow $flow, array $triggerConfig, ?string $userId = null): FlowRun;

    /**
     * Advance a contact to the next node(s) after current node completes.
     * Follows connections from source_handle to find target nodes.
     * Dispatches ExecuteFlowNode jobs for each target.
     */
    public function advanceContact(FlowRun $run, FlowRunContact $contact, string $fromNodeUid, string $sourceHandle = 'default'): void;

    /**
     * Pause a running flow. All active contacts stop at their current node.
     * Waiting contacts remain waiting. No new node executions dispatched.
     */
    public function pauseRun(FlowRun $run): void;

    /**
     * Resume a paused flow. Re-dispatches jobs for all active contacts.
     */
    public function resumeRun(FlowRun $run): void;

    /**
     * Cancel a flow run. All contacts marked cancelled.
     */
    public function cancelRun(FlowRun $run): void;

    /**
     * Check if a flow run is complete (all contacts completed/failed/cancelled).
     * If so, transition run to 'completed' and compute final stats.
     */
    public function checkCompletion(FlowRun $run): void;

    /**
     * Resolve variables for a template string.
     * Merges flow-level variables, contact-level variables, and contact fields.
     */
    public function resolveVariables(string $template, FlowRun $run, FlowRunContact $contact): string;
}
```

#### `ExecuteFlowNode` Job (`app/Jobs/Flow/ExecuteFlowNode.php`)

The core queue job. One job = one contact at one node.

```php
class ExecuteFlowNode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public string $queue = 'flows';

    public function __construct(
        public string $flowRunId,
        public string $flowRunContactId,
        public string $nodeUid
    ) {}

    public function handle(FlowRunService $runService, NodeHandlerRegistry $handlers): void
    {
        // 1. Load run, contact, node. Bail if run is paused/cancelled.
        // 2. Update contact: current_node_uid = nodeUid, status = 'active'
        // 3. Log: 'entered' action
        // 4. Resolve the handler for this node type
        // 5. Execute handler: $result = $handler->execute($context)
        // 6. Log: 'succeeded' or 'failed' action with output_data
        // 7. Update contact path array
        // 8. If result->shouldAdvance: $runService->advanceContact(...)
        //    If result->shouldWait: mark contact 'waiting', schedule ResumeFlowContact job
        //    If result->shouldEnd: mark contact 'completed'
        //    If result->shouldFail: mark contact 'failed'
        // 9. Check run completion
    }
}
```

#### `NodeHandlerRegistry` (`app/Services/Flow/NodeHandlerRegistry.php`)

Maps node types to handler classes:

```php
class NodeHandlerRegistry
{
    private array $handlers = [
        // Triggers (just pass through — the trigger already happened)
        'trigger_api'            => TriggerPassthroughHandler::class,
        'trigger_webhook'        => TriggerPassthroughHandler::class,
        'trigger_sms_inbound'    => TriggerPassthroughHandler::class,
        'trigger_rcs_inbound'    => TriggerPassthroughHandler::class,
        'trigger_campaign'       => TriggerPassthroughHandler::class,
        'trigger_contact_event'  => TriggerPassthroughHandler::class,
        'trigger_schedule'       => TriggerPassthroughHandler::class,
        'trigger_audience'       => TriggerPassthroughHandler::class,

        // Actions
        'send_message'   => SendMessageHandler::class,
        'contact'        => ContactActionHandler::class,
        'tag_action'     => TagActionHandler::class,
        'list_action'    => ListActionHandler::class,
        'optout_action'  => OptOutActionHandler::class,
        'webhook'        => WebhookHandler::class,
        'action_group'   => ActionGroupHandler::class,

        // Logic
        'wait'              => WaitHandler::class,
        'decision'          => DecisionHandler::class,
        'decision_contact'  => DecisionContactHandler::class,
        'decision_webhook'  => DecisionWebhookHandler::class,
        'ab_split'          => AbSplitHandler::class,

        // End
        'inbox_handoff' => InboxHandoffHandler::class,
        'flow_handoff'  => FlowHandoffHandler::class,
        'end'           => EndHandler::class,
    ];

    public function resolve(string $nodeType): NodeHandler;
}
```

#### `NodeHandler` Contract (`app/Contracts/Flow/NodeHandler.php`)

```php
interface NodeHandler
{
    /**
     * Execute this node for a single contact.
     *
     * @param NodeExecutionContext $context Contains:
     *   - FlowRun $run
     *   - FlowRunContact $contact
     *   - FlowNode $node (with config)
     *   - array $variables (merged flow + contact variables)
     * @return NodeResult
     */
    public function execute(NodeExecutionContext $context): NodeResult;
}
```

#### `NodeResult` (`app/Services/Flow/NodeResult.php`)

```php
class NodeResult
{
    public string $action;           // 'advance' | 'wait' | 'end' | 'fail'
    public string $sourceHandle;     // 'default', 'yes', 'no', 'success', 'error', 'A', 'B'
    public array $outputVariables;   // Variables to merge into contact.variables
    public ?int $waitSeconds;        // For 'wait' action — schedule resume after N seconds
    public ?string $waitEvent;       // For 'wait' action — resume on event (reply, DLR)
    public ?string $errorMessage;    // For 'fail' action

    public static function advance(string $handle = 'default', array $vars = []): self;
    public static function wait(int $seconds, array $vars = []): self;
    public static function waitForEvent(string $event, int $timeoutSeconds, array $vars = []): self;
    public static function end(array $vars = []): self;
    public static function fail(string $message): self;
}
```

### Graph Traversal Logic

```
advanceContact(run, contact, fromNodeUid, sourceHandle):
    1. Find connections WHERE source_node_uid = fromNodeUid AND source_handle = sourceHandle
    2. For each connection:
       a. Look up the target node
       b. Dispatch ExecuteFlowNode job for (run, contact, targetNodeUid)
    3. If no connections found:
       a. Mark contact as 'completed' (natural end of branch)
    4. If multiple connections (fan-out):
       a. This only happens with ab_split — contact follows ONE branch (already determined)
       b. For all other nodes, there should be exactly one connection per handle
```

### Quiet Hours / Send Rate

```
SendMessageHandler:
    1. Check node.config.quiet_hours
    2. If quiet hours active (e.g. 20:00-09:00) and current time is within:
       → Return NodeResult::wait(seconds_until_quiet_hours_end)
    3. Check flow run send rate limit (from flow.config or system default)
    4. If rate limited:
       → Release job back to queue with delay
    5. Otherwise: send message, return advance
```

---

## 4. Audience Trigger Node

### Node Type Definition (JS)

```javascript
trigger_audience: {
    label: 'Audience',
    icon: 'fa-users',
    category: 'trigger',
    outputs: ['default'],
    inputs: false,
    description: 'Select contacts to enter this flow',
    configFields: [],
    customProperties: true   // Uses iframe-based audience selector
}
```

This is the **22nd node type**. It must be added to:
1. `NODE_TYPES` in `flow-builder.js`
2. `FlowNode::getCategory()` in PHP
3. `FlowBuilderController::save()` validation whitelist
4. Node palette in `builder.blade.php` (Triggers section)

### How It Works

The audience trigger reuses the **campaign iframe** (`/messages/send?context=flow&mode=audience`):

```
┌─────────────────────────────────────────────────────┐
│  Audience Trigger — Properties Panel                │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │  Summary                                      │  │
│  │  • 3 Contact Lists selected (4,281 contacts)  │  │
│  │  • 2 Tags selected                            │  │
│  │  • 147 manual numbers                         │  │
│  │  • 89 opted-out (will be excluded)            │  │
│  │  ─────────────────────────────────             │  │
│  │  Estimated audience: 4,339 unique contacts     │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  ┌───────────────────────────────────────────────┐  │
│  │  ☐ De-duplicate across sources                │  │
│  │  ☐ Screen against opt-out lists               │  │
│  │  ☐ Validate phone numbers                     │  │
│  └───────────────────────────────────────────────┘  │
│                                                     │
│  [ Configure Audience ]  ← opens iframe modal       │
│                                                     │
│  Send Rate: [ 100 ] messages per [ minute ▼]        │
│  ☐ Respect quiet hours (20:00 – 09:00)              │
└─────────────────────────────────────────────────────┘
```

### Iframe Communication (postMessage)

Reuses the same pattern as `send_message`:

```javascript
// flow-builder.js → iframe
{ type: 'flowEmbedReady' }
{ type: 'flowRestoreConfig', config: { recipient_sources: {...} } }

// iframe → flow-builder.js
{ type: 'flowAudienceApplied', config: {
    recipient_sources: {
        contact_list_ids: [uuid, uuid],
        tag_ids: [uuid, uuid],
        phone_numbers: ['27...', '27...'],
        uploaded_file_id: uuid | null
    },
    audience_summary: {
        total_contacts: 4281,
        total_unique: 4339,
        total_opted_out: 89,
        total_invalid: 12,
        source_breakdown: [
            { type: 'contact_list', id: uuid, name: 'VIP Customers', count: 2100 },
            { type: 'tag', id: uuid, name: 'Active', count: 2181 },
            { type: 'manual', count: 147 }
        ]
    }
}}
{ type: 'flowAudienceCancelled' }
```

### Backend — Audience Resolution

When a flow with `trigger_audience` is activated/run:

```php
// FlowRunService::startRun()
if ($triggerNode->type === 'trigger_audience') {
    $sources = $triggerNode->config['recipient_sources'];

    // Reuse CampaignService's recipient resolution logic
    $resolver = app(RecipientResolverService::class);
    $recipients = $resolver->resolve(
        accountId: $run->account_id,
        contactListIds: $sources['contact_list_ids'] ?? [],
        tagIds: $sources['tag_ids'] ?? [],
        phoneNumbers: $sources['phone_numbers'] ?? [],
        uploadedFileId: $sources['uploaded_file_id'] ?? null,
        dedup: $triggerNode->config['deduplicate'] ?? true,
        screenOptOuts: $triggerNode->config['screen_optouts'] ?? true
    );

    // Create FlowRunContact records in batches
    foreach (array_chunk($recipients, 1000) as $batch) {
        FlowRunContact::insert(array_map(fn($r) => [
            'id' => Str::uuid(),
            'flow_run_id' => $run->id,
            'contact_id' => $r['contact_id'],
            'mobile_number' => $r['mobile_number'],
            'status' => 'pending',
            'variables' => json_encode([
                'contact' => $r['contact_data'] // first_name, last_name, etc.
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ], $batch));
    }

    // Update audience_summary on the run
    $run->update(['audience_summary' => $audienceSummary]);

    // Dispatch ExecuteFlowNode jobs for each contact → first real node
    // (The trigger itself is passthrough — advance to its output connection)
}
```

---

## 5. Variable System

### Variable Scopes

```
┌─────────────────────────────────────────────┐
│  Flow-Level Variables (flow_runs.variables)  │
│  Set once at trigger time. Available to all  │
│  contacts. Read-only during execution.       │
│                                              │
│  • trigger.payload.*   (API/webhook body)    │
│  • trigger.timestamp                         │
│  • flow.id, flow.name                        │
│  • run.id                                    │
└─────────────────────────────────────────────┘
        │
┌───────▼─────────────────────────────────────┐
│  Contact-Level Variables                     │
│  (flow_run_contacts.variables)               │
│  Per-contact. Updated as the contact moves   │
│  through nodes. Each node can add variables. │
│                                              │
│  • contact.first_name, .last_name, .email    │
│  • contact.mobile_number                     │
│  • contact.custom_data.*                     │
│  • webhook_response.{node_label}.*           │
│  • message.{node_label}.message_id           │
│  • message.{node_label}.status               │
│  • decision.{node_label}.result (yes/no)     │
│  • ab.{node_label}.branch (A/B/C)            │
└─────────────────────────────────────────────┘
```

### Template Resolution

```php
// FlowRunService::resolveVariables()
// Input: "Hi {{contact.first_name}}, your order {{trigger.payload.order_id}} is on its way!"
// Output: "Hi Adrian, your order ORD-12345 is on its way!"

public function resolveVariables(string $template, FlowRun $run, FlowRunContact $contact): string
{
    $vars = array_merge(
        $run->variables ?? [],         // Flow-level (trigger.*, flow.*, run.*)
        $contact->variables ?? [],     // Contact-level (contact.*, webhook_response.*, etc.)
    );

    return preg_replace_callback('/\{\{([a-zA-Z0-9_.]+)\}\}/', function ($matches) use ($vars) {
        return data_get($vars, $matches[1], $matches[0]); // Keep original if unresolved
    }, $template);
}
```

### Variable Picker (Frontend)

When editing a text field in any node's properties, a small `{{ }}` button appears that opens a dropdown of available variables. The available variables depend on the node's position in the graph:

```javascript
_getAvailableVariables(nodeId) {
    var vars = [
        // Always available
        { key: 'contact.first_name', label: 'First Name' },
        { key: 'contact.last_name', label: 'Last Name' },
        { key: 'contact.mobile_number', label: 'Phone Number' },
        { key: 'contact.email', label: 'Email' },
        { key: 'flow.name', label: 'Flow Name' },
        { key: 'run.id', label: 'Run ID' },
    ];

    // Walk upstream from this node and collect outputs
    var upstream = this._getUpstreamNodes(nodeId);
    upstream.forEach(function(upNode) {
        if (upNode.type === 'trigger_api' || upNode.type === 'trigger_webhook') {
            vars.push({ key: 'trigger.payload', label: 'Trigger Payload (JSON)' });
        }
        if (upNode.type === 'webhook') {
            var label = upNode.config.label || upNode.label || 'webhook';
            vars.push({ key: 'webhook_response.' + label + '.status', label: label + ' Status Code' });
            vars.push({ key: 'webhook_response.' + label + '.body', label: label + ' Response Body' });
        }
        if (upNode.type === 'send_message') {
            var label = upNode.config.label || upNode.label || 'message';
            vars.push({ key: 'message.' + label + '.message_id', label: label + ' Message ID' });
        }
    });

    return vars;
}
```

---

## 6. A/B Split Node

### Node Type Definition (JS)

```javascript
ab_split: {
    label: 'A/B Split',
    icon: 'fa-code-fork',         // or fa-random with different color
    category: 'logic',
    outputs: ['A', 'B'],          // Dynamic — can be extended to A/B/C
    inputs: true,
    description: 'Split contacts into groups for testing',
    configFields: [],
    customProperties: true
}
```

This is the **23rd node type**.

### Properties Panel

```
┌──────────────────────────────────────────────────┐
│  A/B Split — Properties                          │
│                                                  │
│  Split Name: [ Price Test         ]              │
│                                                  │
│  Branches:                                       │
│  ┌────────────────────────────────────────────┐  │
│  │ A  [ Control         ]   [ 50 ]%           │  │
│  │ B  [ Discount Offer  ]   [ 50 ]%           │  │
│  │                               [+ Add Branch]│  │
│  └────────────────────────────────────────────┘  │
│                                                  │
│  Split Method:                                   │
│  (•) Random       ( ) Round-robin                │
│                                                  │
│  ☐ Auto-select winner after [ 24 ] hours         │
│    Winner metric: [ Delivery Rate ▼]             │
└──────────────────────────────────────────────────┘
```

### Output Ports

Rendered dynamically based on branch count (2-4 branches):

```css
/* 2 branches: A at 30%, B at 70% */
.port-output-A { left: 30%; border-color: #1565C0; }
.port-output-B { left: 70%; border-color: #EF6C00; }

/* 3 branches: A at 20%, B at 50%, C at 80% */
.port-output-C { left: 80%; border-color: #2E7D32; }
```

### Runtime Handler

```php
class AbSplitHandler implements NodeHandler
{
    public function execute(NodeExecutionContext $ctx): NodeResult
    {
        $config = $ctx->node->config;
        $branches = $config['branches']; // [{key: 'A', weight: 50}, {key: 'B', weight: 50}]

        // Deterministic assignment based on contact ID for consistency
        // (same contact always goes to same branch if flow is re-run)
        $branch = $this->assignBranch($ctx->contact->id, $branches);

        // Record assignment
        $ctx->contact->update([
            'ab_assignments' => array_merge(
                $ctx->contact->ab_assignments ?? [],
                [$ctx->node->node_uid => $branch]
            )
        ]);

        // Update A/B results counter
        FlowAbResult::updateOrCreate(
            ['flow_run_id' => $ctx->run->id, 'node_uid' => $ctx->node->node_uid, 'branch' => $branch],
            ['contact_count' => DB::raw('contact_count + 1')]
        );

        return NodeResult::advance($branch, ['ab.' . ($config['name'] ?? 'split') . '.branch' => $branch]);
    }

    private function assignBranch(string $contactId, array $branches): string
    {
        // Weighted random using contact ID as seed for determinism
        $hash = crc32($contactId);
        $roll = $hash % 100;
        $cumulative = 0;
        foreach ($branches as $branch) {
            $cumulative += $branch['weight'];
            if ($roll < $cumulative) {
                return $branch['key'];
            }
        }
        return end($branches)['key'];
    }
}
```

---

## 7. Live Flow Monitor

### Canvas Overlay

When a flow run is active, the canvas shows real-time stats overlaid on nodes:

```
┌─────────────────────────┐
│ 🔌 API Trigger          │
│ ────────────────────    │
│ 4,339 entered           │  ← Badge showing total contacts that entered
└────────────┬────────────┘
             │
┌────────────▼────────────┐
│ ✉️ Send Welcome SMS     │
│ ────────────────────    │
│ 🟢 3,891 sent           │  ← Green = success count
│ 🔴   112 failed         │  ← Red = failure count
│ ⏳   336 waiting         │  ← Yellow = waiting (rate limit/quiet hours)
└────────────┬────────────┘
             │
┌────────────▼────────────┐
│ ⏱️ Wait 24h             │
│ ────────────────────    │
│ ⏳ 3,891 waiting         │  ← All waiting for timer
│ Resumes: 16 Mar 09:00   │
└─────────────────────────┘
```

### Implementation

**Backend endpoint**: `GET /flows/{id}/runs/{runId}/stats`

```json
{
    "run": {
        "id": "uuid",
        "status": "running",
        "started_at": "2026-03-16T10:00:00Z",
        "stats": {
            "contacts_total": 4339,
            "contacts_active": 336,
            "contacts_waiting": 3891,
            "contacts_completed": 0,
            "contacts_failed": 112,
            "messages_sent": 3891,
            "messages_delivered": 3450,
            "total_cost": 234.56
        }
    },
    "node_stats": {
        "node_abc123": { "entered": 4339, "active": 0, "succeeded": 4339, "failed": 0 },
        "node_def456": { "entered": 4339, "active": 0, "succeeded": 3891, "failed": 112, "waiting": 336 },
        "node_ghi789": { "entered": 3891, "active": 0, "waiting": 3891 }
    }
}
```

**Frontend**: Poll every 5 seconds while run is active. Render count badges on nodes:

```javascript
_updateMonitorOverlay(stats) {
    Object.keys(stats.node_stats).forEach(function(nodeUid) {
        var nodeStat = stats.node_stats[nodeUid];
        var el = document.querySelector('[data-node-uid="' + nodeUid + '"] .monitor-badge');
        if (!el) {
            el = document.createElement('div');
            el.className = 'monitor-badge';
            document.querySelector('[data-node-uid="' + nodeUid + '"]').appendChild(el);
        }
        el.innerHTML = this._formatNodeStats(nodeStat);
    }.bind(this));
}
```

### Click-to-Inspect

Clicking a node while monitoring opens a contact list for that node:

```
┌─────────────────────────────────────────────────┐
│  Contacts at "Send Welcome SMS" (3,891)         │
│                                                 │
│  Status: [All ▼]  Search: [_______________]     │
│                                                 │
│  Phone          Name           Status    Time   │
│  +27 82 123... Adrian Smith   ✅ Sent    10:02  │
│  +27 83 456... Jane Doe       ✅ Sent    10:02  │
│  +27 84 789... Bob Wilson     ❌ Failed  10:03  │
│  ...                                            │
│                                  [Export CSV]    │
└─────────────────────────────────────────────────┘
```

---

## 8. Flow Analytics Dashboard

### Dashboard View

A new tab/view alongside the canvas: `GET /flows/{id}/analytics`

```
┌──────────────────────────────────────────────────────────────┐
│  Flow: Welcome Journey           [Canvas] [Analytics] [Runs]│
│                                                              │
│  ┌──────────────────────────────────────────────────────┐    │
│  │  Conversion Funnel                                    │    │
│  │  ████████████████████████████████████ 4,339 entered   │    │
│  │  ██████████████████████████████       3,891 sent      │    │
│  │  ███████████████████████              3,450 delivered │    │
│  │  ████████                             1,247 replied   │    │
│  │  ██████                                 891 completed │    │
│  └──────────────────────────────────────────────────────┘    │
│                                                              │
│  ┌────────────────────┐  ┌────────────────────┐              │
│  │ Delivery Rate      │  │ Reply Rate         │              │
│  │     88.7%          │  │     32.1%          │              │
│  └────────────────────┘  └────────────────────┘              │
│                                                              │
│  ┌────────────────────┐  ┌────────────────────┐              │
│  │ Total Cost         │  │ Avg Time to        │              │
│  │     R 234.56       │  │ Complete: 26.4h    │              │
│  └────────────────────┘  └────────────────────┘              │
│                                                              │
│  A/B Test Results: "Price Test"                              │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  Branch A (Control):    50% │ 85.2% delivered │ 28% │    │
│  │  Branch B (Discount):   50% │ 86.1% delivered │ 41% │ ★  │
│  └─────────────────────────────────────────────────────┘    │
│                                                              │
│  Run History                                                 │
│  ┌─────────────────────────────────────────────────────┐    │
│  │  Run #3  16 Mar 10:00  Running   4,339 contacts     │    │
│  │  Run #2  14 Mar 09:00  Completed 2,100 contacts     │    │
│  │  Run #1  12 Mar 14:30  Completed 1,500 contacts     │    │
│  └─────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────┘
```

### Data Aggregation

Stats computed from `flow_run_logs` and `flow_run_contacts`:

```php
class FlowAnalyticsService
{
    public function getFunnel(FlowRun $run): array;
    public function getNodeStats(FlowRun $run): array;
    public function getAbResults(FlowRun $run): array;
    public function getTimeline(FlowRun $run): array;
    public function getRunHistory(Flow $flow): Collection;
    public function getCostBreakdown(FlowRun $run): array;
}
```

---

## 9. Runtime Node Handlers

### Handler Summary

| Handler | Node Type(s) | What It Does | Output Handle |
|---------|-------------|-------------|---------------|
| `TriggerPassthroughHandler` | All 8 triggers | No-op, just advances | `default` |
| `SendMessageHandler` | `send_message` | Resolves variables in content, calls `DeliveryService`, optionally waits for reply | `default`, or dynamic interaction ports |
| `ContactActionHandler` | `contact` | Creates/updates/deletes contact via `ContactService` | `default` |
| `TagActionHandler` | `tag_action` | Adds/removes tag from contact | `default` |
| `ListActionHandler` | `list_action` | Adds/removes contact from list | `default` |
| `OptOutActionHandler` | `optout_action` | Adds/removes opt-out record | `default` |
| `WebhookHandler` | `webhook` | Makes HTTP request, stores response in variables | `success` or `error` |
| `ActionGroupHandler` | `action_group` | Runs steps sequentially (tag, list, optout, contact update, wait) | `default` |
| `WaitHandler` | `wait` | Schedules delayed resume | `default` (after delay) |
| `DecisionHandler` | `decision` | Evaluates condition against contact/variables | `yes` or `no` |
| `DecisionContactHandler` | `decision_contact` | Evaluates contact book condition (in_list, has_tag, etc.) | `yes` or `no` |
| `DecisionWebhookHandler` | `decision_webhook` | Makes HTTP call, evaluates response condition | `yes` or `no` |
| `AbSplitHandler` | `ab_split` | Assigns contact to branch by weight | `A`, `B`, `C`, etc. |
| `InboxHandoffHandler` | `inbox_handoff` | Creates/updates InboxConversation, marks contact completed | (none — end node) |
| `FlowHandoffHandler` | `flow_handoff` | Starts a new FlowRun on the target flow for this contact | (none — end node) |
| `EndHandler` | `end` | Marks contact completed | (none — end node) |

### Key Handler Details

#### `SendMessageHandler`

```php
class SendMessageHandler implements NodeHandler
{
    public function execute(NodeExecutionContext $ctx): NodeResult
    {
        $config = $ctx->node->config;
        $contact = $ctx->contact;

        // 1. Resolve variables in message content
        $content = $ctx->resolveVariables($config['sms_content'] ?? '');
        $rcsPayload = $this->resolveRcsVariables($config['rcs_payload'] ?? null, $ctx);

        // 2. Check quiet hours
        if ($config['quiet_hours'] ?? false) {
            $resumeAt = $this->getQuietHoursEnd();
            if ($resumeAt) {
                return NodeResult::wait($resumeAt->diffInSeconds(now()));
            }
        }

        // 3. Build delivery request (reuse DeliveryService)
        $channel = $config['channel'] ?? 'sms';
        $senderId = $config['sender_id'] ?? null;
        $rcsAgentId = $config['rcs_agent_id'] ?? null;

        try {
            $result = $this->deliveryService->sendFlowMessage(
                accountId: $ctx->run->account_id,
                mobileNumber: $contact->mobile_number,
                channel: $channel,
                content: $content,
                rcsPayload: $rcsPayload,
                senderId: $senderId,
                rcsAgentId: $rcsAgentId,
                flowRunId: $ctx->run->id,
                nodeUid: $ctx->node->node_uid
            );

            $vars = [
                'message.' . ($config['label'] ?? 'message') . '.message_id' => $result->messageId,
                'message.' . ($config['label'] ?? 'message') . '.status' => 'sent',
            ];

            // 4. If interaction enabled, wait for reply
            if ($config['interaction_enabled'] ?? false) {
                $timeout = $config['interaction_timeout'] ?? ['value' => 48, 'unit' => 'hours'];
                $seconds = $this->toSeconds($timeout);
                return NodeResult::waitForEvent('sms_reply:' . $contact->mobile_number, $seconds, $vars);
            }

            return NodeResult::advance('default', $vars);

        } catch (\Exception $e) {
            return NodeResult::fail('Send failed: ' . $e->getMessage());
        }
    }
}
```

#### `WebhookHandler`

```php
class WebhookHandler implements NodeHandler
{
    public function execute(NodeExecutionContext $ctx): NodeResult
    {
        $config = $ctx->node->config;

        // 1. Resolve variables in URL, headers, body
        $url = $ctx->resolveVariables($config['url'] ?? '');
        $body = $ctx->resolveVariables($config['body_template'] ?? '');
        $headers = $this->resolveHeaders($config['headers'] ?? [], $ctx);

        // 2. Load credential if specified
        if ($config['credential_id'] ?? null) {
            $credential = ApiCredential::find($config['credential_id']);
            if ($credential) {
                $headers = $this->applyCredential($headers, $credential);
                $credential->update(['last_used_at' => now()]);
            }
        }

        // 3. Make HTTP request
        try {
            $response = Http::timeout($config['timeout'] ?? 30)
                ->withHeaders($headers)
                ->send($config['method'] ?? 'POST', $url, ['body' => $body]);

            $varPrefix = 'webhook_response.' . ($config['label'] ?? 'webhook');
            $vars = [
                $varPrefix . '.status' => $response->status(),
                $varPrefix . '.body' => $response->json() ?? $response->body(),
                $varPrefix . '.success' => $response->successful(),
            ];

            if ($response->successful()) {
                return NodeResult::advance('success', $vars);
            } else {
                return NodeResult::advance('error', $vars);
            }
        } catch (\Exception $e) {
            return NodeResult::advance('error', [
                'webhook_response.' . ($config['label'] ?? 'webhook') . '.error' => $e->getMessage()
            ]);
        }
    }
}
```

#### `WaitHandler`

```php
class WaitHandler implements NodeHandler
{
    public function execute(NodeExecutionContext $ctx): NodeResult
    {
        $config = $ctx->node->config;
        $waitType = $config['wait_type'] ?? 'duration';

        if ($waitType === 'duration') {
            $value = (int)($config['duration_value'] ?? 1);
            $unit = $config['duration_unit'] ?? 'hours';
            $seconds = match($unit) {
                'minutes' => $value * 60,
                'hours'   => $value * 3600,
                'days'    => $value * 86400,
            };

            // Respect quiet hours if enabled
            if ($config['quiet_hours'] ?? false) {
                $resumeAt = now()->addSeconds($seconds);
                $quietEnd = $this->getQuietHoursEnd($resumeAt);
                if ($quietEnd && $quietEnd->gt($resumeAt)) {
                    $seconds = $quietEnd->diffInSeconds(now());
                }
            }

            return NodeResult::wait($seconds);
        }

        if ($waitType === 'until_date') {
            $target = Carbon::parse($config['target_date']);
            $seconds = max(0, $target->diffInSeconds(now()));
            return NodeResult::wait($seconds);
        }

        if ($waitType === 'until_event') {
            $timeout = (int)($config['timeout_hours'] ?? 48) * 3600;
            return NodeResult::waitForEvent($config['event_name'], $timeout);
        }

        return NodeResult::advance(); // Fallback
    }
}
```

### Resume Job

```php
// app/Jobs/Flow/ResumeFlowContact.php
class ResumeFlowContact implements ShouldQueue
{
    public function __construct(
        public string $flowRunContactId,
        public string $nodeUid
    ) {}

    public function handle(FlowRunService $runService): void
    {
        $contact = FlowRunContact::find($this->flowRunContactId);
        if (!$contact || $contact->status !== 'waiting') return;

        $run = $contact->flowRun;
        if ($run->status !== 'running') return;

        $contact->update(['status' => 'active']);
        $runService->advanceContact($run, $contact, $this->nodeUid, 'default');
    }
}

// Dispatched with delay:
ResumeFlowContact::dispatch($contact->id, $nodeUid)
    ->delay(now()->addSeconds($waitSeconds))
    ->onQueue('flows');
```

---

## 10. Frontend Changes

### New/Modified UI Components

| Component | Type | Description |
|-----------|------|-------------|
| Audience trigger properties | New renderer | iframe audience selector + summary panel |
| A/B Split properties | New renderer | Branch editor with weights + method selector |
| Variable picker | New component | `{{ }}` button on text fields, dropdown of available vars |
| Monitor overlay | New component | Count badges on nodes, status bar, polling |
| Analytics tab | New view | Funnel chart, stats cards, A/B results, run history |
| Run controls | New toolbar section | Start Run, Pause, Resume, Cancel buttons |
| Contact inspector | New modal | List contacts at a specific node with status/time |

### Node Palette Addition

```html
<!-- Triggers section -->
<div class="palette-node" draggable="true" data-type="trigger_audience">
    <i class="fas fa-users palette-icon"></i>
    <span>Audience</span>
</div>

<!-- Logic section -->
<div class="palette-node" draggable="true" data-type="ab_split">
    <i class="fas fa-code-fork palette-icon"></i>
    <span>A/B Split</span>
</div>
```

### Run Control Toolbar

When a flow is active, the toolbar shows run controls:

```html
<div class="flow-run-controls" id="flowRunControls" style="display:none;">
    <span class="run-status-badge" id="runStatusBadge">Running</span>
    <span class="run-contacts" id="runContactCount">4,339 contacts</span>
    <button class="btn btn-sm btn-outline-warning" id="pauseRunBtn">
        <i class="fas fa-pause"></i> Pause
    </button>
    <button class="btn btn-sm btn-outline-danger" id="cancelRunBtn">
        <i class="fas fa-stop"></i> Cancel
    </button>
</div>
```

---

## 11. Routes & Controllers

### New Routes

```php
// Flow execution
Route::post('/flows/{id}/run', [FlowRunController::class, 'start']);
Route::put('/flows/runs/{runId}/pause', [FlowRunController::class, 'pause']);
Route::put('/flows/runs/{runId}/resume', [FlowRunController::class, 'resume']);
Route::put('/flows/runs/{runId}/cancel', [FlowRunController::class, 'cancel']);

// Monitoring
Route::get('/flows/{id}/runs', [FlowRunController::class, 'index']);
Route::get('/flows/{id}/runs/{runId}/stats', [FlowRunController::class, 'stats']);
Route::get('/flows/{id}/runs/{runId}/node/{nodeUid}/contacts', [FlowRunController::class, 'nodeContacts']);

// Analytics
Route::get('/flows/{id}/analytics', [FlowAnalyticsController::class, 'index']);
Route::get('/flows/{id}/analytics/funnel', [FlowAnalyticsController::class, 'funnel']);
Route::get('/flows/{id}/analytics/ab-results', [FlowAnalyticsController::class, 'abResults']);

// Audience (if /messages/send needs a separate mode)
Route::get('/messages/send', [MessageController::class, 'send']);
    // ?context=flow&mode=audience — renders audience-only selector
```

### New Controllers

**`FlowRunController`** — Run lifecycle (start, pause, resume, cancel) + monitoring endpoints

**`FlowAnalyticsController`** — Analytics data aggregation endpoints

### New Services

| Service | Purpose |
|---------|---------|
| `FlowRunService` | Orchestrator — start, advance, pause, resume, cancel |
| `FlowAnalyticsService` | Funnel computation, A/B aggregation, cost breakdown |
| `NodeHandlerRegistry` | Maps node types → handler classes |

### New Jobs

| Job | Queue | Purpose |
|-----|-------|---------|
| `ExecuteFlowNode` | `flows` | Execute one node for one contact |
| `ResumeFlowContact` | `flows` | Resume a waiting contact after delay |
| `CheckFlowRunCompletion` | `flows` | Periodic check if run is complete |

---

## 12. Anti-Drift Rules (Phase 3 Additions)

### DO

1. **Reuse `DeliveryService`** for all message sending — never build a parallel sending path
2. **Reuse `RecipientResolverService`** for audience resolution — never duplicate dedup/optout logic
3. **One job per contact per node** — never process multiple contacts in a single job (isolation)
4. **Always check run status before executing** — if paused/cancelled, bail immediately
5. **Always check contact status before executing** — if cancelled/opted_out, skip
6. **Store variables as flat dot-notation keys** in JSONB — `webhook_response.tracking.url`, not nested objects
7. **Log every node execution** in `flow_run_logs` — this is the audit trail and analytics source
8. **Use `DB::raw('counter + 1')`** for atomic counter increments on stats (not read-modify-write)
9. **Scope all FlowRun queries to account_id** — same tenant isolation pattern as Phase 2

### DO NOT

1. **Never process contacts synchronously** — always dispatch queue jobs
2. **Never store message content in `flow_run_logs`** — store only metadata (message_id, status, cost). Content lives in `MessageLog`
3. **Never allow running a draft flow** — status must be 'active' to start a run
4. **Never modify flow nodes/connections while a run is active** — enforce this in `FlowBuilderController::save()`
5. **Never fan-out contacts at decision/split nodes** — one contact follows exactly one branch
6. **Never block the queue** with long-running webhook calls — use `Http::timeout(30)` max
7. **Never expose `flow_run_contacts.variables` in list endpoints** — it may contain PII. Only expose in detail view with permission check

### When Adding New Node Types (Updated for Phase 3)

All Phase 2 rules still apply, plus:

8. Create a `NodeHandler` class in `app/Services/Flow/Handlers/`
9. Register it in `NodeHandlerRegistry`
10. Define what variables the handler outputs (document in handler class docblock)
11. If the handler needs to wait (async), implement the wait/resume pattern via `ResumeFlowContact`

---

## 13. Implementation Order

### Wave 1 — Foundation (Engine + Schema)

```
1. Database migrations (flow_runs, flow_run_contacts, flow_run_logs, flow_ab_results)
2. Models (FlowRun, FlowRunContact, FlowRunLog, FlowAbResult)
3. NodeHandler contract + NodeResult
4. NodeHandlerRegistry
5. Simple handlers: TriggerPassthroughHandler, EndHandler, WaitHandler
6. FlowRunService (startRun, advanceContact, checkCompletion)
7. ExecuteFlowNode job
8. ResumeFlowContact job
9. FlowRunController (start, pause, resume, cancel)
```

**Milestone**: Can run a simple flow (Trigger → Wait → End) with a single hardcoded contact.

### Wave 2 — Audience Trigger + Sending

```
10. trigger_audience node type (JS, PHP model, PHP validation — the 22nd type)
11. Audience trigger properties renderer (iframe integration)
12. /messages/send?context=flow&mode=audience iframe mode
13. Audience resolution in FlowRunService (reuse RecipientResolverService)
14. SendMessageHandler (integrate with DeliveryService)
15. Run controls in toolbar (Start Run button, Pause, Cancel)
```

**Milestone**: Can build a flow with audience trigger + send message, select contacts, and run it.

### Wave 3 — All Handlers

```
16. ContactActionHandler
17. TagActionHandler
18. ListActionHandler
19. OptOutActionHandler
20. WebhookHandler (with credential loading)
21. ActionGroupHandler (runs steps sequentially)
22. DecisionHandler
23. DecisionContactHandler
24. DecisionWebhookHandler
25. InboxHandoffHandler
26. FlowHandoffHandler
```

**Milestone**: All 21 existing node types execute at runtime.

### Wave 4 — Variables

```
27. Variable resolution in FlowRunService
28. Variable output from each handler (merge into contact.variables)
29. Variable picker UI component
30. Variable insertion in text fields ({{ }} button)
31. Upstream variable detection (_getAvailableVariables)
```

**Milestone**: Variables flow between nodes. Messages can use `{{contact.first_name}}`.

### Wave 5 — A/B Split

```
32. ab_split node type (JS, PHP model, PHP validation — the 23rd type)
33. A/B Split properties renderer (branch editor)
34. AbSplitHandler
35. Dynamic output ports for A/B/C branches
36. FlowAbResult tracking
```

**Milestone**: Can split contacts into branches and track which performs better.

### Wave 6 — Live Monitor

```
37. Stats endpoint (FlowRunController::stats)
38. Monitor overlay component (badges on nodes)
39. Polling logic (5-second interval while run active)
40. Contact inspector modal (click node → see contacts)
41. Node contacts endpoint
42. Run status bar in toolbar
```

**Milestone**: Real-time visibility into running flows.

### Wave 7 — Analytics

```
43. FlowAnalyticsService (funnel, timeline, cost)
44. FlowAnalyticsController
45. Analytics tab view (funnel chart, stats cards)
46. A/B results view
47. Run history list
48. Export (CSV of contacts/results)
```

**Milestone**: Full analytics dashboard with conversion funnel and A/B comparison.

---

## Appendix: File Map (Phase 3 New Files)

```
app/
  Contracts/Flow/
    NodeHandler.php                     # Handler interface
  Services/Flow/
    FlowRunService.php                  # Orchestrator
    FlowAnalyticsService.php            # Analytics aggregation
    NodeHandlerRegistry.php             # Type → handler mapping
    NodeResult.php                      # Handler return value
    NodeExecutionContext.php            # Context passed to handlers
    Handlers/
      TriggerPassthroughHandler.php
      SendMessageHandler.php
      ContactActionHandler.php
      TagActionHandler.php
      ListActionHandler.php
      OptOutActionHandler.php
      WebhookHandler.php
      ActionGroupHandler.php
      WaitHandler.php
      DecisionHandler.php
      DecisionContactHandler.php
      DecisionWebhookHandler.php
      AbSplitHandler.php
      InboxHandoffHandler.php
      FlowHandoffHandler.php
      EndHandler.php
  Http/Controllers/
    FlowRunController.php               # Run lifecycle + monitoring
    FlowAnalyticsController.php         # Analytics endpoints
  Jobs/Flow/
    ExecuteFlowNode.php                 # Core execution job
    ResumeFlowContact.php              # Delayed resume job
    CheckFlowRunCompletion.php          # Completion checker
  Models/
    FlowRun.php
    FlowRunContact.php
    FlowRunLog.php
    FlowAbResult.php

database/migrations/
    2026_03_16_000020_create_flow_runs_table.php
    2026_03_16_000021_create_flow_run_contacts_table.php
    2026_03_16_000022_create_flow_run_logs_table.php
    2026_03_16_000023_create_flow_ab_results_table.php
    2026_03_16_000024_add_run_columns_to_flows_table.php

resources/views/quicksms/flows/
    analytics.blade.php                 # Analytics dashboard view

public/
    js/flow-monitor.js                  # Live monitoring overlay (separate file)
    css/flow-monitor.css                # Monitor styles (separate file)
```

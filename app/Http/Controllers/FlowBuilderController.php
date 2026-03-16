<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Flow;
use App\Models\FlowNode;
use App\Models\FlowConnection;
use Illuminate\Support\Facades\DB;

class FlowBuilderController extends Controller
{
    /**
     * Flow list page.
     */
    public function index()
    {
        $accountId = session('customer_tenant_id');

        $flows = Flow::where('account_id', $accountId)
            ->orderByDesc('updated_at')
            ->get();

        return view('quicksms.flows.index', [
            'page_title' => 'Flow Builder',
            'flows' => $flows,
        ]);
    }

    /**
     * Show the visual flow builder canvas.
     */
    public function builder($id = null)
    {
        $accountId = session('customer_tenant_id');
        $flow = null;

        if ($id) {
            $flow = Flow::where('account_id', $accountId)
                ->with(['nodes', 'connections'])
                ->findOrFail($id);
        }

        $sender_ids = [];
        if ($accountId) {
            $senderIds = \App\Models\SenderId::where('account_id', $accountId)
                ->where('workflow_status', 'approved')
                ->orderByDesc('is_default')
                ->orderBy('sender_id_value')
                ->get();

            foreach ($senderIds as $s) {
                $sender_ids[] = [
                    'id' => $s->uuid,
                    'name' => $s->sender_id_value,
                    'type' => strtolower($s->sender_type === 'ALPHA' ? 'alphanumeric' : ($s->sender_type === 'NUMERIC' ? 'numeric' : 'shortcode')),
                ];
            }
        }
        if (empty($sender_ids)) {
            $sender_ids = [['id' => '00000000-0000-0000-0000-000000000000', 'name' => 'QuickSMS', 'type' => 'alphanumeric']];
        }

        $userId = session('customer_user_id');
        $user = \App\Models\User::withoutGlobalScope('tenant')->find($userId);
        $rcs_agents = $user
            ? \App\Models\RcsAgent::usableByUser($user)
                ->select('id', 'uuid', 'name', 'description', 'brand_color', 'logo_url')
                ->get()
                ->map(fn($a) => [
                    'id'          => $a->uuid,
                    'name'        => $a->name,
                    'logo'        => $a->logo_url ?: null,
                    'tagline'     => $a->description ?? '',
                    'brand_color' => $a->brand_color ?? '#886CC0',
                ])
                ->toArray()
            : [];

        // Contact Lists
        $contactLists = \App\Models\ContactList::where('account_id', $accountId)
            ->orderBy('name')
            ->get()
            ->map(fn($l) => ['id' => $l->id, 'name' => $l->name])
            ->toArray();

        // Tags
        $tags = \App\Models\Tag::where('account_id', $accountId)
            ->orderBy('name')
            ->get()
            ->map(fn($t) => ['id' => $t->id, 'name' => $t->name])
            ->toArray();

        // Opt-Out Lists
        $optOutLists = \App\Models\OptOutList::where('account_id', $accountId)
            ->orderBy('name')
            ->get()
            ->map(fn($o) => ['id' => $o->id, 'name' => $o->name])
            ->toArray();

        // Active Flows (for flow handoff node)
        $activeFlows = Flow::where('account_id', $accountId)
            ->when($id, fn($q) => $q->where('id', '!=', $id))
            ->orderBy('name')
            ->get()
            ->map(fn($f) => ['id' => $f->id, 'name' => $f->name])
            ->toArray();

        // API Credentials
        $apiCredentials = [];
        if (class_exists(\App\Models\ApiCredential::class)) {
            $apiCredentials = \App\Models\ApiCredential::where('account_id', $accountId)
                ->orderBy('name')
                ->get()
                ->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'auth_type' => $c->auth_type])
                ->toArray();
        }

        return view('quicksms.flows.builder', [
            'page_title' => $flow ? 'Edit Flow: ' . $flow->name : 'New Flow',
            'flow' => $flow,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'contact_lists' => $contactLists,
            'tags' => $tags,
            'opt_out_lists' => $optOutLists,
            'active_flows' => $activeFlows,
            'api_credentials' => $apiCredentials,
        ]);
    }

    /**
     * Create a new flow.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $flow = Flow::create([
            'account_id' => session('customer_tenant_id'),
            'created_by' => session('customer_user_id'),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'flow' => $flow,
            'redirect' => route('flows.builder', $flow->id),
        ]);
    }

    /**
     * Save the full flow (nodes + connections) from the canvas.
     */
    public function save(Request $request, $id)
    {
        $accountId = session('customer_tenant_id');
        $flow = Flow::where('account_id', $accountId)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'nodes' => 'required|array',
            'nodes.*.node_uid' => 'required|string|max:64',
            'nodes.*.type' => 'required|in:trigger_api,trigger_webhook,trigger_sms_inbound,trigger_rcs_inbound,trigger_campaign,trigger_contact_event,trigger_schedule,send_message,contact,tag_action,list_action,optout_action,webhook,action_group,wait,decision,decision_contact,decision_webhook,inbox_handoff,flow_handoff,end',
            'nodes.*.label' => 'nullable|string|max:255',
            'nodes.*.config' => 'nullable|array',
            'nodes.*.position_x' => 'required|numeric',
            'nodes.*.position_y' => 'required|numeric',
            'connections' => 'present|array',
            'connections.*.source_node_uid' => 'required|string|max:64',
            'connections.*.target_node_uid' => 'required|string|max:64',
            'connections.*.source_handle' => 'nullable|string|max:50',
            'connections.*.label' => 'nullable|string|max:255',
            'canvas_meta' => 'nullable|array',
        ]);

        // Validate action_group step types
        $allowedStepTypes = ['add_tag', 'remove_tag', 'add_to_list', 'remove_from_list', 'add_optout', 'remove_optout', 'update_contact', 'wait'];
        foreach ($request->input('nodes', []) as $i => $node) {
            if (($node['type'] ?? '') === 'action_group' && !empty($node['config']['steps'])) {
                foreach ($node['config']['steps'] as $j => $step) {
                    if (!in_array($step['type'] ?? '', $allowedStepTypes, true)) {
                        throw ValidationException::withMessages([
                            "nodes.{$i}.config.steps.{$j}.type" => 'Invalid step type.',
                        ]);
                    }
                }
            }
        }

        $nodeUids = collect($request->nodes)->pluck('node_uid')->toArray();
        foreach ($request->connections as $conn) {
            if (!in_array($conn['source_node_uid'], $nodeUids) || !in_array($conn['target_node_uid'], $nodeUids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection references a node that does not exist in the flow.',
                ], 422);
            }
        }

        DB::transaction(function () use ($flow, $request) {
            if ($request->has('name')) {
                $flow->name = $request->name;
            }
            if ($request->has('description')) {
                $flow->description = $request->description;
            }
            if ($request->has('canvas_meta')) {
                $flow->canvas_meta = $request->canvas_meta;
            }
            $flow->save();

            // Replace all nodes
            $flow->nodes()->delete();
            foreach ($request->nodes as $nodeData) {
                $flow->nodes()->create([
                    'node_uid' => $nodeData['node_uid'],
                    'type' => $nodeData['type'],
                    'label' => $nodeData['label'] ?? null,
                    'config' => $nodeData['config'] ?? null,
                    'position_x' => $nodeData['position_x'],
                    'position_y' => $nodeData['position_y'],
                ]);
            }

            // Replace all connections
            $flow->connections()->delete();
            foreach ($request->connections as $connData) {
                $flow->connections()->create([
                    'source_node_uid' => $connData['source_node_uid'],
                    'target_node_uid' => $connData['target_node_uid'],
                    'source_handle' => $connData['source_handle'] ?? 'default',
                    'label' => $connData['label'] ?? null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Flow saved successfully.',
        ]);
    }

    /**
     * Load flow data as JSON (for the canvas).
     */
    public function load($id)
    {
        $accountId = session('customer_tenant_id');
        $flow = Flow::where('account_id', $accountId)
            ->with(['nodes', 'connections'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'flow' => [
                'id' => $flow->id,
                'name' => $flow->name,
                'description' => $flow->description,
                'status' => $flow->status,
                'version' => $flow->version,
                'canvas_meta' => $flow->canvas_meta,
                'nodes' => $flow->nodes->map(function ($node) {
                    return [
                        'node_uid' => $node->node_uid,
                        'type' => $node->type,
                        'label' => $node->label,
                        'config' => $node->config,
                        'position_x' => $node->position_x,
                        'position_y' => $node->position_y,
                    ];
                }),
                'connections' => $flow->connections->map(function ($conn) {
                    return [
                        'source_node_uid' => $conn->source_node_uid,
                        'target_node_uid' => $conn->target_node_uid,
                        'source_handle' => $conn->source_handle,
                        'label' => $conn->label,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Update flow status (activate, pause, archive).
     */
    public function updateStatus(Request $request, $id)
    {
        $accountId = session('customer_tenant_id');
        $flow = Flow::where('account_id', $accountId)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:draft,active,paused,archived',
        ]);

        $flow->status = $request->status;

        if ($request->status === 'active') {
            $flow->last_activated_at = now();
        }

        $flow->save();

        return response()->json([
            'success' => true,
            'message' => 'Flow status updated.',
        ]);
    }

    /**
     * Duplicate a flow.
     */
    public function duplicate($id)
    {
        $accountId = session('customer_tenant_id');
        $flow = Flow::where('account_id', $accountId)
            ->with(['nodes', 'connections'])
            ->findOrFail($id);

        $newFlow = DB::transaction(function () use ($flow) {
            $newFlow = $flow->replicate();
            $newFlow->name = $flow->name . ' (Copy)';
            $newFlow->status = 'draft';
            $newFlow->created_by = session('customer_user_id');
            $newFlow->save();

            foreach ($flow->nodes as $node) {
                $newNode = $node->replicate();
                $newNode->flow_id = $newFlow->id;
                $newNode->save();
            }

            foreach ($flow->connections as $conn) {
                $newConn = $conn->replicate();
                $newConn->flow_id = $newFlow->id;
                $newConn->save();
            }

            return $newFlow;
        });

        return response()->json([
            'success' => true,
            'flow' => $newFlow,
            'redirect' => route('flows.builder', $newFlow->id),
        ]);
    }

    /**
     * Delete a flow.
     */
    public function destroy($id)
    {
        $accountId = session('customer_tenant_id');
        $flow = Flow::where('account_id', $accountId)->findOrFail($id);
        $flow->delete();

        return response()->json([
            'success' => true,
            'message' => 'Flow deleted.',
        ]);
    }
}

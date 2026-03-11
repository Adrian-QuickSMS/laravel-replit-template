<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

        return view('quicksms.flows.builder', [
            'page_title' => $flow ? 'Edit Flow: ' . $flow->name : 'New Flow',
            'flow' => $flow,
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
            'nodes.*.type' => 'required|string|max:50',
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

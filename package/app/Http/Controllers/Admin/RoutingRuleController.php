<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoutingRule;
use App\Models\RoutingGatewayWeight;
use App\Models\RoutingAuditLog;
use App\Models\Gateway;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutingRuleController extends Controller
{
    /**
     * Display UK routes
     */
    public function ukRoutes(Request $request)
    {
        $productType = $request->get('product', 'SMS');
        $showBlocked = $request->get('show_blocked', false);

        $query = RoutingRule::ukRoutes()
            ->forProduct($productType)
            ->with(['primaryGateway.supplier', 'gatewayWeights.gateway.supplier']);

        if (!$showBlocked) {
            $query->active();
        }

        $routes = $query->orderBy('destination_name')->get();

        return view('admin.routing-rules.uk-routes', compact('routes', 'productType', 'showBlocked'));
    }

    /**
     * Display international routes
     */
    public function internationalRoutes(Request $request)
    {
        $productType = $request->get('product', 'SMS');
        $showBlocked = $request->get('show_blocked', false);

        $query = RoutingRule::internationalRoutes()
            ->forProduct($productType)
            ->with(['primaryGateway.supplier', 'gatewayWeights.gateway.supplier']);

        if (!$showBlocked) {
            $query->active();
        }

        $routes = $query->orderBy('destination_name')->get();

        return view('admin.routing-rules.international-routes', compact('routes', 'productType', 'showBlocked'));
    }

    /**
     * Get routing rule details
     */
    public function show($id)
    {
        $rule = RoutingRule::with(['primaryGateway', 'gatewayWeights.gateway.supplier'])
            ->findOrFail($id);

        return response()->json($rule);
    }

    /**
     * Add gateway to route
     */
    public function addGateway(Request $request, $ruleId)
    {
        $request->validate([
            'gateway_id' => 'required|exists:gateways,id',
            'weight' => 'required|integer|min:1|max:100',
            'set_primary' => 'boolean',
        ]);

        $rule = RoutingRule::findOrFail($ruleId);
        $gateway = Gateway::findOrFail($request->gateway_id);

        // Check if gateway already exists in route
        $existing = RoutingGatewayWeight::where('routing_rule_id', $ruleId)
            ->where('gateway_id', $request->gateway_id)
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Gateway already in route'], 400);
        }

        DB::beginTransaction();
        try {
            // Create weight entry
            $weight = RoutingGatewayWeight::create([
                'routing_rule_id' => $ruleId,
                'gateway_id' => $request->gateway_id,
                'weight' => $request->weight,
                'route_status' => 'allowed',
                'is_primary' => $request->set_primary ?? false,
                'created_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Set as primary if requested
            if ($request->set_primary) {
                $this->setPrimary($rule, $gateway);
            }

            // Log action
            RoutingAuditLog::logAction('gateway_added', [
                'entity_type' => 'routing_rule',
                'entity_id' => $ruleId,
                'product_type' => $rule->product_type,
                'destination' => $rule->destination_name,
                'new_value' => [
                    'gateway' => $gateway->name,
                    'supplier' => $gateway->supplier->name,
                    'weight' => $request->weight,
                ],
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Change gateway weight
     */
    public function changeWeight(Request $request, $weightId)
    {
        $request->validate([
            'weight' => 'required|integer|min:1|max:100',
        ]);

        $gatewayWeight = RoutingGatewayWeight::findOrFail($weightId);
        $oldWeight = $gatewayWeight->weight;

        DB::beginTransaction();
        try {
            $gatewayWeight->update([
                'weight' => $request->weight,
                'updated_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Log action
            RoutingAuditLog::logAction('weight_changed', [
                'entity_type' => 'routing_gateway_weight',
                'entity_id' => $weightId,
                'product_type' => $gatewayWeight->routingRule->product_type,
                'destination' => $gatewayWeight->routingRule->destination_name,
                'old_value' => ['weight' => $oldWeight],
                'new_value' => ['weight' => $request->weight],
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Set primary gateway
     */
    public function setPrimaryGateway(Request $request, $ruleId)
    {
        $request->validate([
            'gateway_id' => 'required|exists:gateways,id',
        ]);

        $rule = RoutingRule::findOrFail($ruleId);
        $gateway = Gateway::findOrFail($request->gateway_id);
        $oldPrimaryId = $rule->primary_gateway_id;

        DB::beginTransaction();
        try {
            // Update routing rule primary
            $rule->update([
                'primary_gateway_id' => $request->gateway_id,
                'updated_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Update is_primary flags
            RoutingGatewayWeight::where('routing_rule_id', $ruleId)
                ->update(['is_primary' => false]);

            RoutingGatewayWeight::where('routing_rule_id', $ruleId)
                ->where('gateway_id', $request->gateway_id)
                ->update(['is_primary' => true]);

            // Log action
            RoutingAuditLog::logAction('primary_changed', [
                'entity_type' => 'routing_rule',
                'entity_id' => $ruleId,
                'product_type' => $rule->product_type,
                'destination' => $rule->destination_name,
                'old_value' => ['gateway_id' => $oldPrimaryId],
                'new_value' => ['gateway_id' => $request->gateway_id, 'gateway_name' => $gateway->name],
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Block/unblock gateway in route
     */
    public function toggleGatewayStatus($weightId)
    {
        $gatewayWeight = RoutingGatewayWeight::findOrFail($weightId);
        $newStatus = $gatewayWeight->route_status === 'allowed' ? 'blocked' : 'allowed';
        $action = $newStatus === 'blocked' ? 'gateway_blocked' : 'gateway_unblocked';

        DB::beginTransaction();
        try {
            $gatewayWeight->update([
                'route_status' => $newStatus,
                'updated_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Log action
            RoutingAuditLog::logAction($action, [
                'entity_type' => 'routing_gateway_weight',
                'entity_id' => $weightId,
                'product_type' => $gatewayWeight->routingRule->product_type,
                'destination' => $gatewayWeight->routingRule->destination_name,
                'new_value' => [
                    'gateway' => $gatewayWeight->gateway->name,
                    'status' => $newStatus,
                ],
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove gateway from route
     */
    public function removeGateway($weightId)
    {
        $gatewayWeight = RoutingGatewayWeight::findOrFail($weightId);

        // Check if this is the only gateway
        $gatewayCount = RoutingGatewayWeight::where('routing_rule_id', $gatewayWeight->routing_rule_id)
            ->where('route_status', 'allowed')
            ->count();

        if ($gatewayCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the last gateway from route'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $rule = $gatewayWeight->routingRule;
            $gateway = $gatewayWeight->gateway;

            // If this was primary, clear it
            if ($gatewayWeight->is_primary) {
                $rule->update(['primary_gateway_id' => null]);
            }

            $gatewayWeight->delete();

            // Log action
            RoutingAuditLog::logAction('gateway_removed', [
                'entity_type' => 'routing_rule',
                'entity_id' => $rule->id,
                'product_type' => $rule->product_type,
                'destination' => $rule->destination_name,
                'old_value' => [
                    'gateway' => $gateway->name,
                    'weight' => $gatewayWeight->weight,
                ],
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Block/unblock entire destination
     */
    public function toggleDestination($ruleId)
    {
        $rule = RoutingRule::findOrFail($ruleId);
        $newStatus = $rule->status === 'active' ? 'blocked' : 'active';
        $action = $newStatus === 'blocked' ? 'destination_blocked' : 'destination_unblocked';

        DB::beginTransaction();
        try {
            $rule->update([
                'status' => $newStatus,
                'updated_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Log action
            RoutingAuditLog::logAction($action, [
                'entity_type' => 'routing_rule',
                'entity_id' => $ruleId,
                'product_type' => $rule->product_type,
                'destination' => $rule->destination_name,
                'new_value' => ['status' => $newStatus],
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get available gateways for product
     */
    public function getAvailableGateways(Request $request, $ruleId)
    {
        $rule = RoutingRule::findOrFail($ruleId);

        // Get gateways that support this product and aren't already in the route
        $existingGatewayIds = RoutingGatewayWeight::where('routing_rule_id', $ruleId)
            ->pluck('gateway_id');

        $gateways = Gateway::active()
            ->whereNotIn('id', $existingGatewayIds)
            ->with('supplier')
            ->get();

        return response()->json($gateways);
    }

    private function setPrimary($rule, $gateway)
    {
        $rule->update(['primary_gateway_id' => $gateway->id]);

        RoutingGatewayWeight::where('routing_rule_id', $rule->id)
            ->update(['is_primary' => false]);

        RoutingGatewayWeight::where('routing_rule_id', $rule->id)
            ->where('gateway_id', $gateway->id)
            ->update(['is_primary' => true]);
    }
}

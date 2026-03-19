<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Alerting\AlertHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertHistoryController extends Controller
{
    /**
     * GET /api/v1/alerts/history
     */
    public function index(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $query = AlertHistory::forTenant($accountId)
            ->orderBy('created_at', 'desc');

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        if ($severity = $request->input('severity')) {
            $query->ofSeverity($severity);
        }

        if ($triggerKey = $request->input('trigger_key')) {
            $query->forTriggerKey($triggerKey);
        }

        if ($status = $request->input('status')) {
            $query->ofStatus($status);
        }

        if ($since = $request->input('since')) {
            $query->since($since);
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $history = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($history->items())->map->toPortalArray(),
            'pagination' => [
                'total' => $history->total(),
                'per_page' => $history->perPage(),
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v1/alerts/history/summary
     *
     * Get alert counts grouped by category and severity.
     */
    public function summary(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $since = $request->input('since', now()->subDays(7)->toDateTimeString());

        $counts = AlertHistory::forTenant($accountId)
            ->since($since)
            ->selectRaw('category, severity, count(*) as count')
            ->groupBy('category', 'severity')
            ->get();

        $totalDispatched = AlertHistory::forTenant($accountId)
            ->since($since)
            ->ofStatus('dispatched')
            ->count();

        $totalSuppressed = AlertHistory::forTenant($accountId)
            ->since($since)
            ->where('status', 'like', 'suppressed%')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'by_category_severity' => $counts,
                'total_dispatched' => $totalDispatched,
                'total_suppressed' => $totalSuppressed,
                'period_since' => $since,
            ],
        ]);
    }
}

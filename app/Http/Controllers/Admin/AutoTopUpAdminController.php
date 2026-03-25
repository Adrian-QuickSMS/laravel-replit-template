<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Billing\AutoTopUpConfig;
use App\Models\Billing\AutoTopUpEvent;
use App\Services\Billing\AutoTopUpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutoTopUpAdminController extends Controller
{
    public function __construct(
        private AutoTopUpService $autoTopUpService,
    ) {}

    /**
     * GET /admin/billing/auto-topup
     * Render the admin auto top-up management page.
     */
    public function index()
    {
        return view('admin.billing.auto-topup');
    }

    /**
     * GET /admin/api/billing/auto-topup
     * JSON list of accounts with auto top-up configs.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = AutoTopUpConfig::with('account:id,company_name,account_number,email');

        // Filters
        if ($request->has('status')) {
            match ($request->input('status')) {
                'enabled' => $query->where('enabled', true)->where('admin_locked', false),
                'disabled' => $query->where('enabled', false)->where('admin_locked', false),
                'locked' => $query->where('admin_locked', true),
                'failed' => $query->where('consecutive_failure_count', '>', 0),
                default => null,
            };
        }

        // Search (escape LIKE metacharacters to prevent wildcard injection)
        if ($search = $request->input('search')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->whereHas('account', function ($q) use ($escaped) {
                $q->where('company_name', 'ilike', "%{$escaped}%")
                    ->orWhere('account_number', 'ilike', "%{$escaped}%")
                    ->orWhere('email', 'ilike', "%{$escaped}%");
            });
        }

        $configs = $query->orderByDesc('updated_at')->paginate($request->integer('per_page', 25));

        // Batch load daily stats for all accounts on this page (avoids N+1)
        $accountIds = $configs->getCollection()->pluck('account_id')->toArray();
        $today = now()->utc()->startOfDay();
        $dailyStatsMap = [];
        if (!empty($accountIds)) {
            $rows = \App\Models\Billing\AutoTopUpEvent::whereIn('account_id', $accountIds)
                ->where('created_at', '>=', $today)
                ->where('status', \App\Models\Billing\AutoTopUpEvent::STATUS_SUCCEEDED)
                ->where('event_type', \App\Models\Billing\AutoTopUpEvent::TYPE_PAYMENT_SUCCEEDED)
                ->selectRaw('account_id, COUNT(*) as count, COALESCE(SUM(topup_amount), 0) as value')
                ->groupBy('account_id')
                ->get();
            foreach ($rows as $row) {
                $dailyStatsMap[$row->account_id] = ['count' => (int) $row->count, 'value' => $row->value];
            }
        }

        $items = $configs->getCollection()->map(function ($config) use ($dailyStatsMap) {
            $dailyStats = $dailyStatsMap[$config->account_id] ?? ['count' => 0, 'value' => '0.0000'];

            return [
                'id' => $config->id,
                'account_id' => $config->account_id,
                'account_name' => $config->account?->company_name,
                'account_number' => $config->account?->account_number,
                'enabled' => $config->enabled,
                'admin_locked' => $config->admin_locked,
                'admin_locked_reason' => $config->admin_locked_reason,
                'threshold_amount' => $config->threshold_amount,
                'topup_amount' => $config->topup_amount,
                'max_topups_per_day' => $config->max_topups_per_day,
                'daily_topup_cap' => $config->daily_topup_cap,
                'consecutive_failure_count' => $config->consecutive_failure_count,
                'last_triggered_at' => $config->last_triggered_at?->toIso8601String(),
                'last_successful_topup_at' => $config->last_successful_topup_at?->toIso8601String(),
                'card_brand' => $config->card_brand,
                'card_last4' => $config->card_last4,
                'daily_stats' => $dailyStats,
                'updated_at' => $config->updated_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $configs->currentPage(),
                'last_page' => $configs->lastPage(),
                'total' => $configs->total(),
            ],
        ]);
    }

    /**
     * GET /admin/api/billing/auto-topup/{accountId}/events
     * Event history for a specific account (admin view with Stripe refs).
     */
    public function apiEvents(Request $request, string $accountId): JsonResponse
    {
        $events = AutoTopUpEvent::where('account_id', $accountId)
            ->orderByDesc('created_at')
            ->limit($request->integer('limit', 100))
            ->get()
            ->map(fn ($e) => $e->toAdminArray());

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * POST /admin/api/billing/auto-topup/{accountId}/disable
     * Admin disables and locks auto top-up for an account.
     */
    public function adminDisable(Request $request, string $accountId): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $adminUserId = session('admin_user_id');
        if (!$adminUserId) {
            return response()->json(['success' => false, 'message' => 'Admin session not found.'], 401);
        }

        $this->autoTopUpService->adminDisable($accountId, $adminUserId, $request->input('reason'));

        return response()->json(['success' => true, 'message' => 'Auto top-up has been disabled and locked.']);
    }

    /**
     * POST /admin/api/billing/auto-topup/{accountId}/unlock
     * Admin unlocks auto top-up for an account (does not re-enable).
     */
    public function adminUnlock(Request $request, string $accountId): JsonResponse
    {
        $adminUserId = session('admin_user_id');
        if (!$adminUserId) {
            return response()->json(['success' => false, 'message' => 'Admin session not found.'], 401);
        }

        $this->autoTopUpService->adminUnlock($accountId, $adminUserId);

        return response()->json(['success' => true, 'message' => 'Auto top-up has been unlocked. Customer can now re-enable.']);
    }
}

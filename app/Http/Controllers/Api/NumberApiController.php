<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NumberAssignment;
use App\Models\NumberAutoReplyRule;
use App\Models\PurchasedNumber;
use App\Models\ShortcodeKeyword;
use App\Models\SubAccount;
use App\Models\User;
use App\Models\VmnPoolNumber;
use App\Services\Numbers\NumberService;
use App\Services\Numbers\NumberBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * NumberApiController — customer portal API for the Numbers module.
 *
 * Endpoints:
 *  1. GET    /api/numbers                          — list purchased numbers (Numbers Library)
 *  2. GET    /api/numbers/{id}                     — show single number detail
 *  3. GET    /api/numbers/pool                     — browse available VMNs from pool
 *  4. POST   /api/numbers/purchase-vmn             — purchase VMNs from pool
 *  5. POST   /api/numbers/purchase-keyword         — purchase keyword on shortcode
 *  6. DELETE /api/numbers/{id}                     — release a number
 *  7. PUT    /api/numbers/{id}/configure           — update number configuration
 *  8. POST   /api/numbers/{id}/assign              — assign number to sub-account/user
 *  9. DELETE /api/numbers/assignments/{id}         — remove assignment
 * 10. GET    /api/numbers/{id}/auto-reply-rules    — list auto-reply rules
 * 11. POST   /api/numbers/{id}/auto-reply-rules    — create auto-reply rule
 * 12. PUT    /api/numbers/auto-reply-rules/{id}    — update auto-reply rule
 * 13. DELETE /api/numbers/auto-reply-rules/{id}    — delete auto-reply rule
 * 14. POST   /api/numbers/bulk-assign              — bulk assign numbers
 * 15. POST   /api/numbers/bulk-release             — bulk release numbers
 * 16. GET    /api/numbers/export                   — export to CSV
 * 17. GET    /api/numbers/pricing                  — get pricing for VMNs and keywords
 */
class NumberApiController extends Controller
{
    public function __construct(
        private NumberService $numberService,
        private NumberBillingService $billingService,
    ) {}

    // =====================================================
    // 1. LIST — Numbers Library
    // =====================================================

    public function index(Request $request): JsonResponse
    {
        $query = PurchasedNumber::query()
            ->withCount(['assignments', 'autoReplyRules']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('number_type')) {
            $query->where('number_type', $request->input('number_type'));
        }
        if ($request->filled('country_iso')) {
            $query->where('country_iso', $request->input('country_iso'));
        }
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('number', 'ilike', "%{$search}%")
                  ->orWhere('friendly_name', 'ilike', "%{$search}%");
            });
        }

        // Sorting
        $sortField = $request->input('sort', 'purchased_at');
        $sortDir = $request->input('direction', 'desc');
        $allowedSorts = ['number', 'number_type', 'country_iso', 'status', 'purchased_at', 'last_used_at', 'monthly_fee'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => collect($paginated->items())->map->toPortalArray(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
            'summary' => [
                'total_active' => PurchasedNumber::active()->count(),
                'total_vmns' => PurchasedNumber::active()->vmns()->count(),
                'total_shortcodes' => PurchasedNumber::active()->shortcodes()->count(),
                'total_suspended' => PurchasedNumber::suspended()->count(),
            ],
        ]);
    }

    // =====================================================
    // 2. SHOW — single number detail
    // =====================================================

    public function show(string $id): JsonResponse
    {
        $number = PurchasedNumber::with(['assignments', 'keywords', 'autoReplyRules'])
            ->withCount(['assignments', 'autoReplyRules'])
            ->findOrFail($id);

        return response()->json([
            'data' => $number->toPortalArray(),
            'assignments' => $number->assignments->map(function ($a) {
                return [
                    'id' => $a->id,
                    'assignable_type' => class_basename($a->assignable_type),
                    'assignable_id' => $a->assignable_id,
                    'assignable_name' => $a->assignable?->name ?? $a->assignable?->first_name ?? 'Unknown',
                    'assigned_by' => $a->assigned_by,
                    'created_at' => $a->created_at?->toIso8601String(),
                ];
            }),
            'auto_reply_rules' => $number->autoReplyRules->map->toPortalArray(),
        ]);
    }

    // =====================================================
    // 3. POOL — browse available VMNs
    // =====================================================

    public function pool(Request $request): JsonResponse
    {
        $query = VmnPoolNumber::available();

        if ($request->filled('country_iso')) {
            $query->forCountry($request->input('country_iso'));
        }
        if ($request->filled('number_type')) {
            $query->where('number_type', $request->input('number_type'));
        }
        if ($request->filled('search')) {
            $query->where('number', 'ilike', '%' . $request->input('search') . '%');
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $paginated = $query->orderBy('country_iso')->orderBy('number')->paginate($perPage);

        // Get account for pricing preview
        $accountId = session('customer_tenant_id');
        $account = $accountId ? \App\Models\Account::find($accountId) : null;

        return response()->json([
            'data' => collect($paginated->items())->map(function ($pn) use ($account) {
                $item = [
                    'id' => $pn->id,
                    'number' => $pn->number,
                    'country_iso' => $pn->country_iso,
                    'number_type' => $pn->number_type,
                    'capabilities' => $pn->capabilities,
                ];

                // Include pricing preview if we have account context
                if ($account) {
                    try {
                        $pricing = app(NumberBillingService::class)
                            ->calculateVmnPricing($account, collect([$pn]));
                        $item['setup_fee'] = $pricing['items'][$pn->id]['setup_fee'] ?? null;
                        $item['monthly_fee'] = $pricing['items'][$pn->id]['monthly_fee'] ?? null;
                        $item['currency'] = $pricing['currency'];
                    } catch (\Exception $e) {
                        // Pricing unavailable
                    }
                }

                return $item;
            }),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    // =====================================================
    // 4. PURCHASE VMN
    // =====================================================

    public function purchaseVmn(Request $request): JsonResponse
    {
        $request->validate([
            'pool_number_ids' => 'required|array|min:1|max:100',
            'pool_number_ids.*' => 'uuid',
        ]);

        $user = $this->getAuthenticatedUser();
        $accountId = session('customer_tenant_id');

        if (!$accountId) {
            return response()->json(['error' => 'No account context'], 403);
        }

        try {
            $result = $this->numberService->purchaseVmns(
                $accountId,
                $request->input('pool_number_ids'),
                $user
            );

            return response()->json([
                'success' => true,
                'message' => count($result['purchased_numbers']) . ' number(s) purchased successfully.',
                'data' => collect($result['purchased_numbers'])->map->toPortalArray(),
                'pricing' => $result['pricing'],
                'audit_id' => $result['audit_log']->audit_id,
            ]);
        } catch (\App\Exceptions\Billing\InsufficientBalanceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Insufficient balance for this purchase.',
                'required' => $e->requiredAmount ?? null,
                'available' => $e->availableAmount ?? null,
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    // =====================================================
    // 5. PURCHASE KEYWORD
    // =====================================================

    public function purchaseKeyword(Request $request): JsonResponse
    {
        $request->validate([
            'shortcode_number_id' => 'required|uuid',
            'keyword' => 'required|string|min:2|max:30|regex:/^[A-Za-z0-9]+$/',
        ]);

        $user = $this->getAuthenticatedUser();
        $accountId = session('customer_tenant_id');

        if (!$accountId) {
            return response()->json(['error' => 'No account context'], 403);
        }

        try {
            $result = $this->numberService->purchaseKeyword(
                $accountId,
                $request->input('shortcode_number_id'),
                $request->input('keyword'),
                $user
            );

            return response()->json([
                'success' => true,
                'message' => "Keyword '{$result['keyword']->keyword}' purchased successfully.",
                'data' => $result['keyword']->toPortalArray(),
                'pricing' => $result['pricing'],
                'audit_id' => $result['audit_log']->audit_id,
            ]);
        } catch (\App\Exceptions\Billing\InsufficientBalanceException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Insufficient balance for this purchase.',
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    // =====================================================
    // 6. RELEASE NUMBER
    // =====================================================

    public function release(string $id): JsonResponse
    {
        $number = PurchasedNumber::findOrFail($id);

        if (!$number->isActive() && !$number->isSuspended()) {
            return response()->json([
                'error' => 'Number cannot be released in its current status.',
            ], 422);
        }

        try {
            $this->numberService->releaseNumber($number);

            return response()->json([
                'success' => true,
                'message' => "Number {$number->number} has been released.",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // =====================================================
    // 7. CONFIGURE NUMBER
    // =====================================================

    public function configure(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'forwarding_url' => 'nullable|url|max:2000',
            'forwarding_email' => 'nullable|email|max:255',
            'forwarding_auth_headers' => 'nullable|array',
            'forwarding_auth_headers.*.key' => 'required_with:forwarding_auth_headers|string|max:100',
            'forwarding_auth_headers.*.value' => 'required_with:forwarding_auth_headers|string|max:500',
            'retry_policy' => 'nullable|array',
            'retry_policy.max_retries' => 'nullable|integer|min:0|max:10',
            'retry_policy.retry_delay_seconds' => 'nullable|integer|min:1|max:300',
            'retry_policy.backoff_multiplier' => 'nullable|numeric|min:1|max:5',
            'friendly_name' => 'nullable|string|max:255',
        ]);

        $number = PurchasedNumber::findOrFail($id);

        if (!$number->isActive()) {
            return response()->json(['error' => 'Cannot configure a number that is not active.'], 422);
        }

        // Update friendly name if provided
        if ($request->has('friendly_name')) {
            $number->update(['friendly_name' => $request->input('friendly_name')]);
        }

        try {
            $config = $request->only([
                'forwarding_url', 'forwarding_email',
                'forwarding_auth_headers', 'retry_policy',
            ]);
            $config = array_filter($config, fn($v) => $v !== null);

            if (!empty($config)) {
                $number = $this->numberService->configureNumber($number, $config);
            }

            return response()->json([
                'success' => true,
                'data' => $number->toPortalArray(),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // =====================================================
    // 8. ASSIGN NUMBER
    // =====================================================

    public function assign(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'assignable_type' => 'required|string|in:sub_account,user',
            'assignable_id' => 'required|uuid',
        ]);

        $number = PurchasedNumber::findOrFail($id);
        $user = $this->getAuthenticatedUser();

        $assignableType = $request->input('assignable_type') === 'sub_account'
            ? SubAccount::class
            : User::class;

        try {
            $assignment = $this->numberService->assignNumber(
                $number,
                $assignableType,
                $request->input('assignable_id'),
                $user
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $assignment->id,
                    'assignable_type' => class_basename($assignment->assignable_type),
                    'assignable_id' => $assignment->assignable_id,
                    'created_at' => $assignment->created_at?->toIso8601String(),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    // =====================================================
    // 9. REMOVE ASSIGNMENT
    // =====================================================

    public function unassign(string $assignmentId): JsonResponse
    {
        try {
            $this->numberService->unassignNumber($assignmentId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Assignment not found.'], 404);
        }
    }

    // =====================================================
    // 10. LIST AUTO-REPLY RULES
    // =====================================================

    public function autoReplyRules(string $numberId): JsonResponse
    {
        $number = PurchasedNumber::findOrFail($numberId);

        $rules = NumberAutoReplyRule::where('purchased_number_id', $numberId)
            ->orderBy('priority', 'desc')
            ->get();

        return response()->json([
            'data' => $rules->map->toPortalArray(),
        ]);
    }

    // =====================================================
    // 11. CREATE AUTO-REPLY RULE
    // =====================================================

    public function createAutoReplyRule(Request $request, string $numberId): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:1|max:100',
            'reply_content' => 'required|string|min:1|max:1600',
            'match_type' => 'nullable|string|in:exact,starts_with,contains',
            'priority' => 'nullable|integer|min:0|max:1000',
            'charge_for_reply' => 'nullable|boolean',
        ]);

        $number = PurchasedNumber::findOrFail($numberId);

        if (!$number->isActive()) {
            return response()->json(['error' => 'Cannot add rules to an inactive number.'], 422);
        }

        $rule = $this->numberService->addAutoReplyRule(
            $number,
            $request->input('keyword'),
            $request->input('reply_content'),
            $request->input('match_type', 'exact'),
            $request->input('priority', 0),
            $request->boolean('charge_for_reply', true),
        );

        return response()->json([
            'success' => true,
            'data' => $rule->toPortalArray(),
        ], 201);
    }

    // =====================================================
    // 12. UPDATE AUTO-REPLY RULE
    // =====================================================

    public function updateAutoReplyRule(Request $request, string $ruleId): JsonResponse
    {
        $request->validate([
            'keyword' => 'nullable|string|min:1|max:100',
            'reply_content' => 'nullable|string|min:1|max:1600',
            'match_type' => 'nullable|string|in:exact,starts_with,contains',
            'is_active' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0|max:1000',
            'charge_for_reply' => 'nullable|boolean',
        ]);

        $rule = NumberAutoReplyRule::findOrFail($ruleId);

        $rule = $this->numberService->updateAutoReplyRule($rule, $request->all());

        return response()->json([
            'success' => true,
            'data' => $rule->toPortalArray(),
        ]);
    }

    // =====================================================
    // 13. DELETE AUTO-REPLY RULE
    // =====================================================

    public function deleteAutoReplyRule(string $ruleId): JsonResponse
    {
        $rule = NumberAutoReplyRule::findOrFail($ruleId);
        $this->numberService->deleteAutoReplyRule($rule);

        return response()->json(['success' => true]);
    }

    // =====================================================
    // 14. BULK ASSIGN
    // =====================================================

    public function bulkAssign(Request $request): JsonResponse
    {
        $request->validate([
            'number_ids' => 'required|array|min:1|max:500',
            'number_ids.*' => 'uuid',
            'assignable_type' => 'required|string|in:sub_account,user',
            'assignable_id' => 'required|uuid',
        ]);

        $user = $this->getAuthenticatedUser();
        $assignableType = $request->input('assignable_type') === 'sub_account'
            ? SubAccount::class
            : User::class;

        $created = $this->numberService->bulkAssign(
            $request->input('number_ids'),
            $assignableType,
            $request->input('assignable_id'),
            $user
        );

        return response()->json([
            'success' => true,
            'assigned' => $created,
            'message' => "{$created} number(s) assigned.",
        ]);
    }

    // =====================================================
    // 15. BULK RELEASE
    // =====================================================

    public function bulkRelease(Request $request): JsonResponse
    {
        $request->validate([
            'number_ids' => 'required|array|min:1|max:100',
            'number_ids.*' => 'uuid',
        ]);

        $released = $this->numberService->bulkRelease($request->input('number_ids'));

        return response()->json([
            'success' => true,
            'released' => $released,
            'message' => "{$released} number(s) released.",
        ]);
    }

    // =====================================================
    // 16. EXPORT CSV
    // =====================================================

    public function export(Request $request)
    {
        $accountId = session('customer_tenant_id');
        if (!$accountId) {
            return response()->json(['error' => 'No account context'], 403);
        }

        $csv = $this->numberService->exportToCsv($accountId, $request->all());

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="numbers-export-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // =====================================================
    // 17. PRICING
    // =====================================================

    public function pricing(): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        if (!$accountId) {
            return response()->json(['error' => 'No account context'], 403);
        }

        $account = \App\Models\Account::findOrFail($accountId);

        // Get sample VMN pricing (use a dummy pool entry)
        try {
            $vmnSetupPrice = app(\App\Services\Billing\PricingEngine::class)
                ->resolvePrice($account, 'virtual_number_setup', 'GB');
            $vmnMonthlyPrice = app(\App\Services\Billing\PricingEngine::class)
                ->resolvePrice($account, 'virtual_number_monthly', 'GB');
        } catch (\Exception $e) {
            $vmnSetupPrice = null;
            $vmnMonthlyPrice = null;
        }

        try {
            $keywordPricing = $this->billingService->calculateKeywordPricing($account);
        } catch (\Exception $e) {
            $keywordPricing = null;
        }

        return response()->json([
            'vmn' => [
                'setup_fee' => $vmnSetupPrice?->unitPrice ?? '10.0000',
                'monthly_fee' => $vmnMonthlyPrice?->unitPrice ?? '8.0000',
                'currency' => $account->currency ?? 'GBP',
            ],
            'keyword' => $keywordPricing ?? [
                'setup_fee' => '25.0000',
                'monthly_fee' => '50.0000',
                'currency' => $account->currency ?? 'GBP',
            ],
        ]);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    private function getAuthenticatedUser(): User
    {
        $userId = session('customer_user_id');
        $user = User::withoutGlobalScope('tenant')->find($userId);
        if (!$user) {
            abort(401, 'Unauthenticated');
        }
        return $user;
    }
}

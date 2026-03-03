<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\PurchasedNumber;
use App\Models\ShortcodeKeyword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin Numbers API Controller
 *
 * Provides a global, cross-tenant view of all purchased numbers and shortcode keywords.
 * All routes protected by AdminIpAllowlist + AdminAuthenticate middleware.
 * Uses withoutGlobalScope('tenant') throughout for admin-level access.
 *
 * Route prefix: /admin/api/numbers
 */
class AdminNumbersApiController extends Controller
{
    // =====================================================
    // LIST
    // =====================================================

    /**
     * GET /admin/api/numbers
     * Returns all VMNs, dedicated shortcodes, and shortcode keywords across all accounts.
     */
    public function index(Request $request): JsonResponse
    {
        $search    = $request->input('search', '');
        $statusFilter  = $request->input('status', []);
        $typeFilter    = $request->input('type', []);
        $countryFilter = $request->input('country', []);
        $modeFilter    = $request->input('mode', []);
        $page     = max(1, (int) $request->input('page', 1));
        $pageSize = min(100, max(1, (int) $request->input('pageSize', 12)));
        $sortBy   = $request->input('sortBy', 'created');
        $sortDir  = strtolower($request->input('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (is_string($statusFilter))  $statusFilter  = [$statusFilter];
        if (is_string($typeFilter))    $typeFilter    = [$typeFilter];
        if (is_string($countryFilter)) $countryFilter = [$countryFilter];
        if (is_string($modeFilter))    $modeFilter    = [$modeFilter];

        // --- Purchased Numbers (VMN + Dedicated Shortcode) ---
        $pnQuery = PurchasedNumber::withoutGlobalScope('tenant')
            ->with('account:id,company_name,trading_name')
            ->whereIn('number_type', [
                PurchasedNumber::TYPE_VMN,
                PurchasedNumber::TYPE_DEDICATED_SHORTCODE,
            ])
            ->whereNull('deleted_at');

        if (!empty($statusFilter)) {
            $pnQuery->whereIn('status', $statusFilter);
        }
        if (!empty($typeFilter)) {
            $mappedTypes = [];
            foreach ($typeFilter as $t) {
                if ($t === 'vmn')                 $mappedTypes[] = PurchasedNumber::TYPE_VMN;
                if ($t === 'dedicated_shortcode')  $mappedTypes[] = PurchasedNumber::TYPE_DEDICATED_SHORTCODE;
            }
            if (!empty($mappedTypes)) {
                $pnQuery->whereIn('number_type', $mappedTypes);
            } else {
                $pnQuery->whereRaw('1 = 0');
            }
        }
        if (!empty($countryFilter)) {
            $pnQuery->whereIn('country_iso', $countryFilter);
        }
        if ($search !== '') {
            $pnQuery->where(function ($q) use ($search) {
                $q->where('number', 'ilike', '%' . $search . '%')
                  ->orWhereHas('account', function ($aq) use ($search) {
                      $aq->where('company_name', 'ilike', '%' . $search . '%')
                         ->orWhere('trading_name', 'ilike', '%' . $search . '%');
                  });
            });
        }

        $purchasedNumbers = $pnQuery->get();

        // --- Shortcode Keywords ---
        $kwQuery = ShortcodeKeyword::withoutGlobalScope('tenant')
            ->with([
                'account:id,company_name,trading_name',
                'purchasedNumber:id,country_iso,number',
            ])
            ->whereNull('deleted_at');

        if (!empty($statusFilter)) {
            $kwQuery->whereIn('status', $statusFilter);
        }
        if (!empty($typeFilter) && !in_array('shortcode_keyword', $typeFilter)) {
            $kwQuery->whereRaw('1 = 0');
        }
        if ($search !== '') {
            $kwQuery->where(function ($q) use ($search) {
                $q->where('keyword', 'ilike', '%' . $search . '%')
                  ->orWhereHas('account', function ($aq) use ($search) {
                      $aq->where('company_name', 'ilike', '%' . $search . '%')
                         ->orWhere('trading_name', 'ilike', '%' . $search . '%');
                  });
            });
        }

        $keywords = $kwQuery->get();

        // --- Build unified rows ---
        $rows = [];
        foreach ($purchasedNumbers as $pn) {
            $row = $this->purchasedNumberToRow($pn);
            if (!empty($modeFilter) && !in_array($row['mode'], $modeFilter)) continue;
            $rows[] = $row;
        }
        foreach ($keywords as $kw) {
            $row = $this->keywordToRow($kw);
            if (!empty($modeFilter) && !in_array($row['mode'], $modeFilter)) continue;
            $rows[] = $row;
        }

        // --- Sort ---
        $sortFieldMap = [
            'number'   => 'number',
            'country'  => 'country',
            'type'     => 'type',
            'status'   => 'status',
            'mode'     => 'mode',
            'account'  => 'account',
            'cost'     => 'cost',
            'supplier' => 'supplier',
            'created'  => 'created',
            'modified' => 'modified',
        ];
        $field = $sortFieldMap[$sortBy] ?? 'created';

        usort($rows, function ($a, $b) use ($field, $sortDir) {
            $aVal = $a[$field] ?? '';
            $bVal = $b[$field] ?? '';
            if (is_string($aVal)) $aVal = strtolower($aVal);
            if (is_string($bVal)) $bVal = strtolower($bVal);
            $cmp = $aVal <=> $bVal;
            return $sortDir === 'desc' ? -$cmp : $cmp;
        });

        // --- Paginate ---
        $total     = count($rows);
        $offset    = ($page - 1) * $pageSize;
        $pageRows  = array_slice($rows, $offset, $pageSize);

        return response()->json([
            'success'    => true,
            'data'       => $pageRows,
            'pagination' => [
                'page'       => $page,
                'pageSize'   => $pageSize,
                'totalCount' => $total,
                'totalPages' => $pageSize > 0 ? (int) ceil($total / $pageSize) : 1,
            ],
        ]);
    }

    /**
     * GET /admin/api/numbers/{id}
     */
    public function show(string $id): JsonResponse
    {
        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['error' => 'Number not found'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        if ($type === 'purchased_number') {
            $entity->load('account:id,company_name,trading_name');
            return response()->json(['data' => $this->purchasedNumberToRow($entity)]);
        }

        $entity->load(['account:id,company_name,trading_name', 'purchasedNumber:id,country_iso,number']);
        return response()->json(['data' => $this->keywordToRow($entity)]);
    }

    // =====================================================
    // SUSPEND / REACTIVATE
    // =====================================================

    /**
     * POST /admin/api/numbers/{id}/suspend
     */
    public function suspend(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        if ($entity->status === 'suspended') {
            return response()->json(['success' => false, 'error' => 'Already suspended', 'code' => 'ALREADY_SUSPENDED'], 422);
        }

        $before = ['status' => $entity->status];
        $entity->status = 'suspended';
        if ($type === 'purchased_number') {
            $entity->suspended_at = now();
        }
        $entity->save();

        $this->logAction($id, $type, 'NUMBER_SUSPENDED', $before, ['status' => 'suspended'], $request->input('reason'));

        $entity->load('account:id,company_name,trading_name');
        return response()->json([
            'success' => true,
            'data'    => $type === 'purchased_number' ? $this->purchasedNumberToRow($entity) : $this->keywordToRow($entity),
            'changes' => ['before' => $before, 'after' => ['status' => 'suspended']],
        ]);
    }

    /**
     * POST /admin/api/numbers/{id}/reactivate
     */
    public function reactivate(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        if ($entity->status !== 'suspended') {
            return response()->json(['success' => false, 'error' => 'Number is not suspended', 'code' => 'NOT_SUSPENDED'], 422);
        }

        $before = ['status' => $entity->status];
        $entity->status = 'active';
        if ($type === 'purchased_number') {
            $entity->suspended_at = null;
        }
        $entity->save();

        $this->logAction($id, $type, 'NUMBER_REACTIVATED', $before, ['status' => 'active'], $request->input('reason'));

        $entity->load('account:id,company_name,trading_name');
        return response()->json([
            'success' => true,
            'data'    => $type === 'purchased_number' ? $this->purchasedNumberToRow($entity) : $this->keywordToRow($entity),
            'changes' => ['before' => $before, 'after' => ['status' => 'active']],
        ]);
    }

    // =====================================================
    // REASSIGN
    // =====================================================

    /**
     * POST /admin/api/numbers/{id}/reassign
     * Body: { accountId, subAccountId, reason }
     */
    public function reassign(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'accountId' => 'required|uuid',
            'reason'    => 'nullable|string|max:500',
        ]);

        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        $targetAccount = Account::find($request->input('accountId'));
        if (!$targetAccount) {
            return response()->json(['success' => false, 'error' => 'Target account not found', 'code' => 'ACCOUNT_NOT_FOUND'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        $before = ['accountId' => $entity->account_id];
        $entity->account_id = $targetAccount->id;
        $entity->save();

        $this->logAction($id, $type, 'NUMBER_REASSIGNED', $before, ['accountId' => $targetAccount->id], $request->input('reason'));

        $entity->load('account:id,company_name,trading_name');
        if ($type === 'purchased_number') {
            $data = $this->purchasedNumberToRow($entity);
        } else {
            $entity->load('purchasedNumber:id,country_iso,number');
            $data = $this->keywordToRow($entity);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
            'changes' => [
                'before' => ['account' => $before['accountId']],
                'after'  => ['account' => $targetAccount->company_name ?? $targetAccount->trading_name],
            ],
        ]);
    }

    // =====================================================
    // MODE / CAPABILITIES / WEBHOOK / OPTOUT ROUTING
    // =====================================================

    /**
     * PUT /admin/api/numbers/{id}/mode
     * Body: { mode, reason }
     */
    public function updateMode(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'mode'   => 'required|in:portal,api',
            'reason' => 'nullable|string|max:500',
        ]);

        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        $config = $entity->configuration ?? [];
        $before = ['mode' => $config['mode'] ?? 'portal'];
        $config['mode'] = $request->input('mode');
        $entity->configuration = $config;
        $entity->save();

        $this->logAction($id, $type, 'NUMBER_MODE_CHANGED', $before, ['mode' => $config['mode']], $request->input('reason'));

        $entity->load('account:id,company_name,trading_name');
        return response()->json([
            'success' => true,
            'data'    => $this->purchasedNumberToRow($entity),
            'changes' => ['before' => $before, 'after' => ['mode' => $config['mode']]],
        ]);
    }

    /**
     * PUT /admin/api/numbers/{id}/capabilities
     * Body: { capabilities, reason }
     */
    public function updateCapabilities(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'capabilities'   => 'required|array',
            'capabilities.*' => 'in:senderid,inbox,optout,api',
            'reason'         => 'nullable|string|max:500',
        ]);

        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        if ($type === 'keyword') {
            $restricted = array_intersect($request->input('capabilities', []), ['senderid', 'inbox']);
            if (!empty($restricted)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Shortcode keywords cannot have SenderID or Inbox capabilities',
                    'code'    => 'KEYWORD_CAPABILITY_RESTRICTION',
                ], 422);
            }
        }

        $config = $entity->configuration ?? [];
        $before = ['capabilities' => $config['capabilities'] ?? []];
        $config['capabilities'] = array_values(array_unique($request->input('capabilities')));
        $entity->configuration = $config;
        $entity->save();

        $this->logAction($id, $type, 'NUMBER_CAPABILITY_CHANGED', $before, ['capabilities' => $config['capabilities']], $request->input('reason'));

        $entity->load('account:id,company_name,trading_name');
        return response()->json([
            'success' => true,
            'data'    => $type === 'purchased_number' ? $this->purchasedNumberToRow($entity) : $this->keywordToRow($entity),
            'changes' => ['before' => $before, 'after' => ['capabilities' => $config['capabilities']]],
        ]);
    }

    /**
     * PUT /admin/api/numbers/{id}/webhook
     * Body: { webhookUrl, reason }
     */
    public function updateWebhook(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'webhookUrl' => 'nullable|url|max:500',
            'reason'     => 'nullable|string|max:500',
        ]);

        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        if ($type === 'purchased_number') {
            $config = $entity->configuration ?? [];
            if (($config['mode'] ?? 'portal') !== 'api') {
                return response()->json(['success' => false, 'error' => 'Webhook URL can only be set for numbers in API mode', 'code' => 'MODE_MISMATCH'], 422);
            }
            $before = ['apiWebhookUrl' => $config['forwarding_url'] ?? null];
            $config['forwarding_url'] = $request->input('webhookUrl') ?: null;
            $entity->configuration = $config;
            $entity->save();
        } else {
            return response()->json(['success' => false, 'error' => 'Webhooks are not supported for keywords', 'code' => 'NOT_SUPPORTED'], 422);
        }

        $this->logAction($id, $type, 'NUMBER_WEBHOOK_UPDATED', $before, ['apiWebhookUrl' => $request->input('webhookUrl')], $request->input('reason'));

        $entity->load('account:id,company_name,trading_name');
        return response()->json([
            'success' => true,
            'data'    => $this->purchasedNumberToRow($entity),
            'changes' => ['before' => $before, 'after' => ['apiWebhookUrl' => $request->input('webhookUrl')]],
        ]);
    }

    /**
     * PUT /admin/api/numbers/{id}/optout-routing
     * Body: { routingConfig, reason }
     */
    public function updateOptoutRouting(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'routingConfig'          => 'required|array',
            'routingConfig.keywords' => 'nullable|string|max:255',
            'routingConfig.reply'    => 'nullable|string|max:500',
            'routingConfig.forward'  => 'nullable|email|max:255',
            'reason'                 => 'nullable|string|max:500',
        ]);

        $found = $this->resolveEntity($id);
        if (!$found) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        ['type' => $type, 'entity' => $entity] = $found;

        $config = $entity->configuration ?? [];
        $before = ['optoutConfig' => $config['optout_config'] ?? null];
        $routing = $request->input('routingConfig');
        $config['optout_config'] = [
            'keywords' => $routing['keywords'] ?? 'STOP, UNSUBSCRIBE',
            'reply'    => $routing['reply'] ?? 'You have been unsubscribed.',
            'forward'  => $routing['forward'] ?? null,
        ];
        $entity->configuration = $config;
        $entity->save();

        $this->logAction($id, $type, 'NUMBER_OPTOUT_ROUTING_UPDATED', $before, ['optoutConfig' => $config['optout_config']], $request->input('reason'));

        $entity->load('account:id,company_name,trading_name');
        return response()->json([
            'success' => true,
            'data'    => $type === 'purchased_number' ? $this->purchasedNumberToRow($entity) : $this->keywordToRow($entity),
            'changes' => ['before' => $before, 'after' => ['optoutConfig' => $config['optout_config']]],
        ]);
    }

    // =====================================================
    // KEYWORD DISABLE
    // =====================================================

    /**
     * POST /admin/api/numbers/{id}/disable-keyword
     */
    public function disableKeyword(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $kw = ShortcodeKeyword::withoutGlobalScope('tenant')->whereNull('deleted_at')->find($id);
        if (!$kw) {
            return response()->json(['success' => false, 'error' => 'Keyword not found', 'code' => 'NOT_FOUND'], 404);
        }

        $before = ['status' => $kw->status];
        $kw->status = 'suspended';
        $kw->save();

        $this->logAction($id, 'keyword', 'KEYWORD_DISABLED', $before, ['status' => 'suspended'], $request->input('reason'));

        $kw->load(['account:id,company_name,trading_name', 'purchasedNumber:id,country_iso,number']);
        return response()->json([
            'success' => true,
            'data'    => $this->keywordToRow($kw),
            'changes' => ['before' => $before, 'after' => ['status' => 'suspended']],
        ]);
    }

    // =====================================================
    // RETURN TO POOL
    // =====================================================

    /**
     * POST /admin/api/numbers/{id}/return-to-pool
     * Clears account_id and sets status to released on a VMN.
     */
    public function returnToPool(Request $request, string $id): JsonResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $pn = PurchasedNumber::withoutGlobalScope('tenant')->whereNull('deleted_at')->find($id);
        if (!$pn) {
            return response()->json(['success' => false, 'error' => 'Number not found', 'code' => 'NOT_FOUND'], 404);
        }

        if ($pn->number_type !== PurchasedNumber::TYPE_VMN) {
            return response()->json(['success' => false, 'error' => 'Only VMNs can be returned to pool', 'code' => 'INVALID_TYPE'], 422);
        }

        $before = ['accountId' => $pn->account_id, 'status' => $pn->status];
        $pn->status     = 'released';
        $pn->released_at = now();
        $pn->account_id = null;
        $pn->save();

        $this->logAction($id, 'purchased_number', 'RETURNED_TO_POOL', $before, ['status' => 'released', 'accountId' => null], $request->input('reason'));

        return response()->json([
            'success' => true,
            'changes' => ['before' => $before, 'after' => ['status' => 'released', 'accountId' => null]],
        ]);
    }

    // =====================================================
    // AUDIT
    // =====================================================

    /**
     * GET /admin/api/numbers/{id}/audit
     */
    public function audit(string $id): JsonResponse
    {
        return response()->json(['data' => [], 'totalCount' => 0]);
    }

    // =====================================================
    // BULK OPERATIONS
    // =====================================================

    /**
     * POST /admin/api/numbers/bulk/reassign
     */
    public function bulkReassign(Request $request): JsonResponse
    {
        $request->validate([
            'ids'       => 'required|array|min:1',
            'ids.*'     => 'uuid',
            'accountId' => 'required|uuid',
            'reason'    => 'nullable|string|max:500',
        ]);

        $targetAccount = Account::find($request->input('accountId'));
        if (!$targetAccount) {
            return response()->json(['success' => false, 'error' => 'Target account not found'], 404);
        }

        $ids     = $request->input('ids');
        $updated = 0;
        $failed  = [];

        foreach ($ids as $id) {
            $found = $this->resolveEntity($id);
            if (!$found) { $failed[] = $id; continue; }
            $found['entity']->account_id = $targetAccount->id;
            $found['entity']->save();
            $updated++;
        }

        return response()->json(['success' => true, 'successCount' => $updated, 'failedCount' => count($failed), 'failedIds' => $failed]);
    }

    /**
     * PUT /admin/api/numbers/bulk/mode
     */
    public function bulkMode(Request $request): JsonResponse
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'uuid',
            'mode'   => 'required|in:portal,api',
            'reason' => 'nullable|string|max:500',
        ]);

        $ids     = $request->input('ids');
        $mode    = $request->input('mode');
        $updated = 0;
        $failed  = [];

        foreach ($ids as $id) {
            $found = $this->resolveEntity($id);
            if (!$found) { $failed[] = $id; continue; }
            $entity = $found['entity'];
            $config = $entity->configuration ?? [];
            $config['mode'] = $mode;
            $entity->configuration = $config;
            $entity->save();
            $updated++;
        }

        return response()->json(['success' => true, 'successCount' => $updated, 'failedCount' => count($failed), 'failedIds' => $failed]);
    }

    /**
     * PUT /admin/api/numbers/bulk/capabilities
     */
    public function bulkCapabilities(Request $request): JsonResponse
    {
        $request->validate([
            'ids'            => 'required|array|min:1',
            'ids.*'          => 'uuid',
            'capabilities'   => 'required|array',
            'capabilities.*' => 'in:senderid,inbox,optout,api',
            'reason'         => 'nullable|string|max:500',
        ]);

        $ids          = $request->input('ids');
        $capabilities = array_values(array_unique($request->input('capabilities')));
        $updated      = 0;
        $failed       = [];

        foreach ($ids as $id) {
            $found = $this->resolveEntity($id);
            if (!$found) { $failed[] = $id; continue; }
            $entity = $found['entity'];
            $config = $entity->configuration ?? [];
            $config['capabilities'] = $capabilities;
            $entity->configuration = $config;
            $entity->save();
            $updated++;
        }

        return response()->json(['success' => true, 'successCount' => $updated, 'failedCount' => count($failed), 'failedIds' => $failed]);
    }

    /**
     * POST /admin/api/numbers/bulk/return-to-pool
     */
    public function bulkReturnToPool(Request $request): JsonResponse
    {
        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'uuid',
            'reason' => 'nullable|string|max:500',
        ]);

        $updated = 0;
        $failed  = [];

        foreach ($request->input('ids') as $id) {
            $pn = PurchasedNumber::withoutGlobalScope('tenant')->whereNull('deleted_at')->find($id);
            if (!$pn || $pn->number_type !== PurchasedNumber::TYPE_VMN) {
                $failed[] = $id;
                continue;
            }
            $pn->status      = 'released';
            $pn->released_at = now();
            $pn->account_id  = null;
            $pn->save();
            $updated++;
        }

        return response()->json(['success' => true, 'successCount' => $updated, 'failedCount' => count($failed), 'failedIds' => $failed]);
    }

    // =====================================================
    // PRIVATE HELPERS
    // =====================================================

    private function resolveEntity(string $id): ?array
    {
        $pn = PurchasedNumber::withoutGlobalScope('tenant')->whereNull('deleted_at')->find($id);
        if ($pn) return ['type' => 'purchased_number', 'entity' => $pn];

        $kw = ShortcodeKeyword::withoutGlobalScope('tenant')->whereNull('deleted_at')->find($id);
        if ($kw) return ['type' => 'keyword', 'entity' => $kw];

        return null;
    }

    private function purchasedNumberToRow(PurchasedNumber $pn): array
    {
        $config  = $pn->configuration ?? [];
        $account = $pn->relationLoaded('account') ? $pn->account : null;

        return [
            'id'           => $pn->id,
            'number'       => $pn->number,
            'country'      => $pn->country_iso,
            'type'         => $pn->number_type,
            'status'       => $pn->status,
            'mode'         => $config['mode'] ?? 'portal',
            'account'      => $account ? ($account->company_name ?? $account->trading_name) : null,
            'accountId'    => $pn->account_id,
            'subAccount'   => null,
            'subAccountId' => null,
            'capabilities' => $config['capabilities'] ?? [],
            'cost'         => (float) ($pn->monthly_fee ?? 0),
            'supplier'     => $config['supplier'] ?? null,
            'route'        => $config['route'] ?? null,
            'network'      => $config['network'] ?? null,
            'portedTo'     => $config['ported_to'] ?? null,
            'apiWebhookUrl'=> $config['forwarding_url'] ?? null,
            'optoutConfig' => $config['optout_config'] ?? null,
            'created'      => $pn->created_at?->format('Y-m-d'),
            'modified'     => $pn->updated_at?->format('Y-m-d'),
        ];
    }

    private function keywordToRow(ShortcodeKeyword $kw): array
    {
        $account      = $kw->relationLoaded('account') ? $kw->account : null;
        $parentNumber = $kw->relationLoaded('purchasedNumber') ? $kw->purchasedNumber : null;

        return [
            'id'             => $kw->id,
            'number'         => $kw->keyword,
            'shortcodeNumber'=> $parentNumber?->number,
            'country'        => $parentNumber?->country_iso ?? 'GB',
            'type'           => 'shortcode_keyword',
            'status'       => $kw->status,
            'mode'         => 'portal',
            'account'      => $account ? ($account->company_name ?? $account->trading_name) : null,
            'accountId'    => $kw->account_id,
            'subAccount'   => null,
            'subAccountId' => null,
            'capabilities' => ['optout'],
            'cost'         => (float) ($kw->monthly_fee ?? 0),
            'supplier'     => null,
            'route'        => null,
            'network'      => null,
            'portedTo'     => null,
            'apiWebhookUrl'=> null,
            'optoutConfig' => null,
            'created'      => $kw->created_at?->format('Y-m-d'),
            'modified'     => $kw->updated_at?->format('Y-m-d'),
        ];
    }

    private function logAction(string $entityId, string $entityType, string $event, array $before, array $after, ?string $reason): void
    {
        Log::info('[AdminNumbers] ' . $event, [
            'entityId'   => $entityId,
            'entityType' => $entityType,
            'before'     => $before,
            'after'      => $after,
            'reason'     => $reason,
        ]);
    }
}

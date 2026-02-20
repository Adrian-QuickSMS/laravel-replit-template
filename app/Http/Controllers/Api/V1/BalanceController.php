<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Billing\BalanceService;
use App\Services\Billing\LedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private LedgerService $ledgerService,
    ) {}

    /**
     * GET /api/v1/balance
     * Current balance summary for authenticated account.
     */
    public function show(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $balance = $this->balanceService->getBalance($accountId);

        return response()->json([
            'success' => true,
            'data' => [
                'currency' => $balance->currency,
                'balance' => $balance->balance,
                'reserved' => $balance->reserved,
                'credit_limit' => $balance->credit_limit,
                'effective_available' => $balance->effective_available,
                'total_outstanding' => $balance->total_outstanding,
                'last_reconciled_at' => $balance->last_reconciled_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/v1/balance/transactions
     * Paginated transaction history.
     */
    public function transactions(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;
        $perPage = min((int)$request->input('per_page', 25), 100);

        $transactions = $this->ledgerService->getTransactionHistory($accountId, $perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/balance/transactions/{id}
     */
    public function transactionDetail(Request $request, string $id): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $entry = \App\Models\Billing\LedgerEntry::where('id', $id)
            ->where('account_id', $accountId)
            ->with('lines')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $entry,
        ]);
    }
}

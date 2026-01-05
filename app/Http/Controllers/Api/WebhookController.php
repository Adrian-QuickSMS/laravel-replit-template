<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WebhookController extends Controller
{
    public function hubspotPayment(Request $request): JsonResponse
    {
        $payload = $request->all();
        
        Log::info('HubSpot payment webhook received', [
            'payload' => $payload,
            'headers' => $request->headers->all(),
        ]);

        // TODO: Verify webhook signature from HubSpot
        // $signature = $request->header('X-HubSpot-Signature');
        
        $eventType = $payload['eventType'] ?? $payload['subscriptionType'] ?? null;
        
        if ($eventType === 'invoice.paid' || $eventType === 'deal.propertyChange') {
            $invoiceId = $payload['objectId'] ?? $payload['invoiceId'] ?? null;
            $accountId = $payload['properties']['hs_external_account_id'] ?? null;
            $amount = $payload['properties']['amount'] ?? 0;
            
            if ($accountId) {
                $this->updateAccountBalance($accountId, $amount);
                $this->notifyPaymentSuccess($accountId, $invoiceId, $amount);
                
                Log::info('Payment processed successfully', [
                    'account_id' => $accountId,
                    'invoice_id' => $invoiceId,
                    'amount' => $amount,
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook processed']);
    }

    public function checkPaymentStatus(Request $request): JsonResponse
    {
        $accountId = $request->get('account_id', 'ACC-001');
        
        $paymentStatus = Cache::get("payment_status_{$accountId}");
        
        if ($paymentStatus) {
            Cache::forget("payment_status_{$accountId}");
            
            return response()->json([
                'success' => true,
                'payment_completed' => true,
                'message' => $paymentStatus['message'] ?? 'Payment successful',
                'amount' => $paymentStatus['amount'] ?? 0,
                'new_balance' => $paymentStatus['new_balance'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'payment_completed' => false,
        ]);
    }

    public function getAccountBalance(Request $request): JsonResponse
    {
        $accountId = $request->get('account_id', 'ACC-001');
        
        // TODO: Replace with actual database query
        $balance = Cache::get("account_balance_{$accountId}", 0);
        
        return response()->json([
            'success' => true,
            'account_id' => $accountId,
            'balance' => $balance,
            'currency' => 'GBP',
        ]);
    }

    private function updateAccountBalance(string $accountId, float $amount): void
    {
        // TODO: Replace with actual database update
        $currentBalance = Cache::get("account_balance_{$accountId}", 0);
        $newBalance = $currentBalance + $amount;
        Cache::put("account_balance_{$accountId}", $newBalance, now()->addDays(30));
        
        Log::info('Account balance updated', [
            'account_id' => $accountId,
            'previous_balance' => $currentBalance,
            'amount_added' => $amount,
            'new_balance' => $newBalance,
        ]);
    }

    private function notifyPaymentSuccess(string $accountId, ?string $invoiceId, float $amount): void
    {
        $newBalance = Cache::get("account_balance_{$accountId}", 0);
        
        Cache::put("payment_status_{$accountId}", [
            'status' => 'success',
            'message' => 'Payment successful. Your balance has been updated.',
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'new_balance' => $newBalance,
            'timestamp' => now()->toIso8601String(),
        ], now()->addMinutes(10));
    }
}

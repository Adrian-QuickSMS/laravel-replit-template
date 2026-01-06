<?php

namespace App\Services;

use App\Models\PurchaseAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Exception;

class NumberPurchaseService
{
    const LOCK_TIMEOUT_SECONDS = 300;
    const LOCK_PREFIX = 'number_lock:';
    const PURCHASE_LOCK_PREFIX = 'purchase_lock:';

    protected HubSpotProductService $hubspotService;

    public function __construct()
    {
        $this->hubspotService = new HubSpotProductService();
    }

    public function acquireLocks(array $items, string $sessionId, int $userId): array
    {
        $lockKey = self::PURCHASE_LOCK_PREFIX . $sessionId;
        
        if (!Cache::lock($lockKey, self::LOCK_TIMEOUT_SECONDS)->get()) {
            throw new Exception('Unable to acquire purchase session lock');
        }

        $lockedItems = [];
        $failedItems = [];

        try {
            DB::beginTransaction();

            foreach ($items as $item) {
                $itemKey = self::LOCK_PREFIX . $item['type'] . ':' . $item['identifier'];
                
                $lock = Cache::lock($itemKey, self::LOCK_TIMEOUT_SECONDS);
                
                if ($lock->get()) {
                    $isAvailable = $this->checkItemAvailability($item['type'], $item['identifier']);
                    
                    if ($isAvailable) {
                        $this->markItemLocked($item['type'], $item['identifier'], $sessionId, $userId);
                        $lockedItems[] = $item;
                    } else {
                        $lock->release();
                        $failedItems[] = [
                            'item' => $item,
                            'reason' => 'Item no longer available'
                        ];
                    }
                } else {
                    $failedItems[] = [
                        'item' => $item,
                        'reason' => 'Item is being purchased by another user'
                    ];
                }
            }

            if (!empty($failedItems)) {
                foreach ($lockedItems as $item) {
                    $this->releaseItemLock($item['type'], $item['identifier'], $sessionId);
                }
                DB::rollBack();
                
                return [
                    'success' => false,
                    'locked_items' => [],
                    'failed_items' => $failedItems,
                    'message' => 'Some items could not be locked for purchase'
                ];
            }

            DB::commit();

            return [
                'success' => true,
                'locked_items' => $lockedItems,
                'failed_items' => [],
                'session_id' => $sessionId,
                'expires_at' => now()->addSeconds(self::LOCK_TIMEOUT_SECONDS)->toIso8601String()
            ];

        } catch (Exception $e) {
            DB::rollBack();
            $this->releaseAllLocks($lockedItems, $sessionId);
            throw $e;
        }
    }

    public function processPurchase(array $purchaseData): array
    {
        $sessionId = $purchaseData['session_id'];
        $userId = $purchaseData['user_id'];
        $subAccountId = $purchaseData['sub_account_id'];
        $items = $purchaseData['items'];
        $purchaseType = $purchaseData['purchase_type'];

        $auditLog = null;

        try {
            DB::beginTransaction();

            $balanceBefore = $this->getAccountBalance($subAccountId);
            
            $pricing = $this->hubspotService->fetchNumbersPricing('GBP');
            $pricingData = $pricing['pricing'] ?? $this->getFallbackPricing();

            $totalSetupFee = 0;
            $totalMonthlyFee = 0;
            $itemDetails = [];

            foreach ($items as $item) {
                $fees = $this->calculateItemFees($item, $pricingData, $purchaseType);
                $totalSetupFee += $fees['setup_fee'];
                $totalMonthlyFee += $fees['monthly_fee'];
                
                $itemDetails[] = [
                    'identifier' => $item['identifier'],
                    'type' => $item['type'] ?? $purchaseType,
                    'setup_fee' => $fees['setup_fee'],
                    'monthly_fee' => $fees['monthly_fee'],
                ];
            }

            if ($balanceBefore < $totalSetupFee) {
                throw new Exception('Insufficient balance for purchase');
            }

            $auditLog = PurchaseAuditLog::create([
                'user_id' => $userId,
                'user_email' => $purchaseData['user_email'] ?? null,
                'user_name' => $purchaseData['user_name'] ?? null,
                'sub_account_id' => $subAccountId,
                'sub_account_name' => $purchaseData['sub_account_name'] ?? null,
                'purchase_type' => $purchaseType,
                'items_purchased' => $itemDetails,
                'pricing_details' => [
                    'source' => $pricing['is_mock'] ? 'fallback' : 'hubspot',
                    'currency' => 'GBP',
                    'pricing_snapshot' => $pricingData,
                ],
                'total_setup_fee' => $totalSetupFee,
                'total_monthly_fee' => $totalMonthlyFee,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $totalSetupFee,
                'status' => 'pending',
                'ip_address' => $purchaseData['ip_address'] ?? null,
                'user_agent' => $purchaseData['user_agent'] ?? null,
                'purchased_at' => now(),
            ]);

            foreach ($items as $item) {
                $this->markItemSold(
                    $item['type'] ?? $purchaseType,
                    $item['identifier'],
                    $subAccountId,
                    $userId
                );
            }

            $balanceAfter = $this->deductBalance($subAccountId, $totalSetupFee);

            $transactionRef = 'TXN-' . strtoupper(Str::random(12));
            
            $auditLog->update([
                'status' => 'completed',
                'transaction_reference' => $transactionRef,
                'balance_after' => $balanceAfter,
            ]);

            DB::commit();

            $this->releaseAllLocks($items, $sessionId);

            return [
                'success' => true,
                'transaction_reference' => $transactionRef,
                'audit_id' => $auditLog->audit_id,
                'items_purchased' => count($items),
                'total_charged' => $totalSetupFee,
                'balance_after' => $balanceAfter,
            ];

        } catch (Exception $e) {
            DB::rollBack();

            if ($auditLog) {
                try {
                    $auditLog->update([
                        'status' => 'failed',
                        'failure_reason' => $e->getMessage(),
                    ]);
                } catch (Exception $logError) {
                }
            }

            $this->releaseAllLocks($items, $sessionId);

            throw $e;
        }
    }

    public function releaseLocks(string $sessionId): void
    {
        $lockData = Cache::get('session_locks:' . $sessionId);
        
        if ($lockData) {
            foreach ($lockData['items'] as $item) {
                $this->releaseItemLock($item['type'], $item['identifier'], $sessionId);
            }
            Cache::forget('session_locks:' . $sessionId);
        }

        Cache::lock(self::PURCHASE_LOCK_PREFIX . $sessionId)->release();
    }

    protected function checkItemAvailability(string $type, string $identifier): bool
    {
        $cacheKey = "availability:{$type}:{$identifier}";
        
        return Cache::remember($cacheKey, 5, function () use ($type, $identifier) {
            return true;
        });
    }

    protected function markItemLocked(string $type, string $identifier, string $sessionId, int $userId): void
    {
        Cache::put(
            "item_lock:{$type}:{$identifier}",
            [
                'session_id' => $sessionId,
                'user_id' => $userId,
                'locked_at' => now()->toIso8601String(),
                'expires_at' => now()->addSeconds(self::LOCK_TIMEOUT_SECONDS)->toIso8601String(),
            ],
            self::LOCK_TIMEOUT_SECONDS
        );
    }

    protected function releaseItemLock(string $type, string $identifier, string $sessionId): void
    {
        $lockKey = self::LOCK_PREFIX . $type . ':' . $identifier;
        Cache::lock($lockKey)->release();
        Cache::forget("item_lock:{$type}:{$identifier}");
    }

    protected function releaseAllLocks(array $items, string $sessionId): void
    {
        foreach ($items as $item) {
            $type = $item['type'] ?? 'vmn';
            $this->releaseItemLock($type, $item['identifier'], $sessionId);
        }
        Cache::lock(self::PURCHASE_LOCK_PREFIX . $sessionId)->release();
    }

    protected function markItemSold(string $type, string $identifier, string $subAccountId, int $userId): void
    {
        Cache::put("item_status:{$type}:{$identifier}", [
            'status' => 'sold',
            'owner_sub_account_id' => $subAccountId,
            'owner_user_id' => $userId,
            'sold_at' => now()->toIso8601String(),
        ], 86400 * 365);
    }

    protected function getAccountBalance(string $subAccountId): float
    {
        return 45.00;
    }

    protected function deductBalance(string $subAccountId, float $amount): float
    {
        $currentBalance = $this->getAccountBalance($subAccountId);
        return $currentBalance - $amount;
    }

    protected function calculateItemFees(array $item, array $pricingData, string $purchaseType): array
    {
        if ($purchaseType === 'keyword') {
            return [
                'setup_fee' => $pricingData['keyword']['setup_fee'] ?? 25.00,
                'monthly_fee' => $pricingData['keyword']['monthly_fee'] ?? 50.00,
            ];
        }

        $pricingKey = $this->getVmnPricingKey($item);
        $vmnPricing = $pricingData['vmn'][$pricingKey] ?? $pricingData['vmn']['uk_longcode'] ?? [];

        return [
            'setup_fee' => $vmnPricing['setup_fee'] ?? 10.00,
            'monthly_fee' => $vmnPricing['monthly_fee'] ?? 8.00,
        ];
    }

    protected function getVmnPricingKey(array $item): string
    {
        $countryCode = $item['country_code'] ?? 'GB';
        $number = $item['identifier'] ?? '';

        if ($countryCode === 'GB') {
            return 'uk_longcode';
        }

        if (preg_match('/^\+1(800|888|877|866|855|844|833)/', $number)) {
            return 'tollfree';
        }

        return 'international';
    }

    protected function getFallbackPricing(): array
    {
        return [
            'vmn' => [
                'uk_longcode' => ['setup_fee' => 10.00, 'monthly_fee' => 8.00],
                'international' => ['setup_fee' => 15.00, 'monthly_fee' => 12.00],
                'tollfree' => ['setup_fee' => 25.00, 'monthly_fee' => 20.00],
            ],
            'keyword' => ['setup_fee' => 25.00, 'monthly_fee' => 50.00],
        ];
    }

    public static function cleanupExpiredLocks(): int
    {
        return 0;
    }
}

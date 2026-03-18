<?php

namespace App\Services;

use App\Models\AccountSettings;
use Illuminate\Support\Facades\Log;

/**
 * DataMaskingService — applies field-level masking to message logs and exports.
 *
 * Maskable fields:
 *   - mobile_number  → 077****0123
 *   - message_content → [REDACTED]
 *   - sent_time      → show date, hide time (e.g. "18/03/2026 --:--")
 *   - delivered_time  → show date, hide time
 *
 * Owner/Admin bypass: when owner_bypass_masking is true and the current user
 * is an owner or admin, masking is skipped.
 *
 * Stateless service — call explicitly in controllers, exports, and API responses.
 */
class DataMaskingService
{
    /**
     * Apply masking to a single message log record (array form).
     *
     * @param array $record  Message log data (from toApiArray or similar)
     * @param string $accountId  The account ID to look up masking config
     * @param array|null $userContext  ['role' => 'owner', 'id' => '...'] — pass null to always mask
     * @return array  The masked record
     */
    public function maskRecord(array $record, string $accountId, ?array $userContext = null): array
    {
        try {
            $config = $this->getMaskingConfig($accountId);

            if (empty($config)) {
                return $record;
            }

            // Check owner/admin bypass
            if ($this->shouldBypassMasking($accountId, $userContext)) {
                return $record;
            }

            return $this->applyMasking($record, $config);

        } catch (\Throwable $e) {
            Log::error('[DataMaskingService] maskRecord failed, returning unmasked', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);
            return $record;
        }
    }

    /**
     * Apply masking to a collection of message log records.
     */
    public function maskCollection(array $records, string $accountId, ?array $userContext = null): array
    {
        try {
            $config = $this->getMaskingConfig($accountId);

            if (empty($config)) {
                return $records;
            }

            if ($this->shouldBypassMasking($accountId, $userContext)) {
                return $records;
            }

            return array_map(fn($record) => $this->applyMasking($record, $config), $records);

        } catch (\Throwable $e) {
            Log::error('[DataMaskingService] maskCollection failed', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);
            return $records;
        }
    }

    /**
     * Mask a mobile number: 07700900123 → 077****0123
     */
    public function maskMobileNumber(string $number): string
    {
        $len = strlen($number);
        if ($len < 7) {
            return str_repeat('*', $len);
        }

        $prefix = substr($number, 0, 3);
        $suffix = substr($number, -4);
        $masked = str_repeat('*', $len - 7);

        return $prefix . $masked . $suffix;
    }

    /**
     * Mask message content entirely.
     */
    public function maskContent(): string
    {
        return '[REDACTED]';
    }

    /**
     * Mask a timestamp: keep date, hide time.
     * "18/03/2026 14:30" → "18/03/2026 --:--"
     */
    public function maskTimestamp(?\DateTimeInterface $timestamp, string $dateFormat = 'd/m/Y'): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        return $timestamp->format($dateFormat) . ' --:--';
    }

    /**
     * Apply masking rules to a record based on config.
     */
    private function applyMasking(array $record, array $config): array
    {
        if (!empty($config['mask_mobile']) && isset($record['mobile_number'])) {
            $record['mobile_number'] = $this->maskMobileNumber($record['mobile_number']);
            // Also mask the raw version if present
            if (isset($record['mobile_number_raw'])) {
                $record['mobile_number_raw'] = null;
            }
        }

        if (!empty($config['mask_content']) && isset($record['content'])) {
            $record['content'] = $this->maskContent();
        }

        if (!empty($config['mask_sent_time']) && isset($record['sent_time']) && $record['sent_time']) {
            // Preserve date portion, mask time
            if ($record['sent_time'] instanceof \DateTimeInterface) {
                $record['sent_time'] = $this->maskTimestamp($record['sent_time']);
            } elseif (is_string($record['sent_time'])) {
                // String format: "18/03/2026 14:30" → "18/03/2026 --:--"
                $record['sent_time'] = preg_replace('/\d{2}:\d{2}$/', '--:--', $record['sent_time']);
            }
        }

        if (!empty($config['mask_delivered_time']) && isset($record['delivery_time']) && $record['delivery_time']) {
            if ($record['delivery_time'] instanceof \DateTimeInterface) {
                $record['delivery_time'] = $this->maskTimestamp($record['delivery_time']);
            } elseif (is_string($record['delivery_time'])) {
                $record['delivery_time'] = preg_replace('/\d{2}:\d{2}$/', '--:--', $record['delivery_time']);
            }
        }

        return $record;
    }

    /**
     * Check if the current user should bypass masking.
     */
    private function shouldBypassMasking(string $accountId, ?array $userContext): bool
    {
        if ($userContext === null) {
            return false;
        }

        $role = $userContext['role'] ?? null;
        if (!in_array($role, ['owner', 'admin'])) {
            return false;
        }

        $settings = $this->getAccountSettings($accountId);
        return $settings && $settings->owner_bypass_masking;
    }

    /**
     * Get the masking config for an account.
     */
    private function getMaskingConfig(string $accountId): array
    {
        $settings = $this->getAccountSettings($accountId);

        if (!$settings) {
            return [];
        }

        $config = $settings->data_masking_config;

        if (is_string($config)) {
            $config = json_decode($config, true) ?: [];
        }

        if (!is_array($config)) {
            return [];
        }

        // Only return config if any masking is actually enabled
        $anyEnabled = ($config['mask_mobile'] ?? false)
            || ($config['mask_content'] ?? false)
            || ($config['mask_sent_time'] ?? false)
            || ($config['mask_delivered_time'] ?? false);

        return $anyEnabled ? $config : [];
    }

    /**
     * Get account settings with in-process caching.
     */
    private function getAccountSettings(string $accountId): ?AccountSettings
    {
        static $cache = [];

        if (!isset($cache[$accountId])) {
            $cache[$accountId] = AccountSettings::withoutGlobalScopes()->find($accountId);
        }

        return $cache[$accountId];
    }
}

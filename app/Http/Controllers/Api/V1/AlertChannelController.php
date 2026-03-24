<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Alerting\AlertChannelConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Validation\WebhookUrlValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AlertChannelController extends Controller
{
    /**
     * GET /api/v1/alerts/channels
     */
    public function index(Request $request): JsonResponse
    {
        $configs = AlertChannelConfig::forTenant($request->user()->tenant_id)
            ->get()
            ->map(function ($config) {
                return [
                    'id' => $config->id,
                    'channel' => $config->channel,
                    'config' => $config->safe_config,
                    'is_enabled' => $config->is_enabled,
                    'updated_at' => $config->updated_at,
                ];
            });

        return response()->json(['success' => true, 'data' => $configs]);
    }

    /**
     * PUT /api/v1/alerts/channels/{channel}
     *
     * Create or update channel configuration.
     */
    public function update(Request $request, string $channel): JsonResponse
    {
        $availableChannels = config('alerting.channels', []);
        if (!in_array($channel, $availableChannels)) {
            return response()->json(['success' => false, 'error' => 'Invalid channel.'], 422);
        }

        $rules = match ($channel) {
            'webhook' => [
                'config.webhook_url' => 'required|url|max:500',
                'config.hmac_secret' => 'sometimes|string|min:16|max:128',
            ],
            'slack' => [
                'config.slack_webhook_url' => 'required|url|max:500',
            ],
            'teams' => [
                'config.teams_webhook_url' => 'required|url|max:500',
            ],
            'email' => [
                'config.email' => 'required|email|max:255',
            ],
            'sms' => [
                'config.phone' => 'required|string|regex:/^[0-9]{10,15}$/',
            ],
            default => [],
        };

        $rules['is_enabled'] = 'sometimes|boolean';

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $configData = $request->input('config', []);

        // SSRF protection — validate webhook URLs resolve to public IPs
        $urlFields = ['webhook_url', 'slack_webhook_url', 'teams_webhook_url'];
        foreach ($urlFields as $field) {
            if (!empty($configData[$field])) {
                $validation = WebhookUrlValidator::validate($configData[$field]);
                if (!$validation['valid']) {
                    return response()->json([
                        'success' => false,
                        'error' => "Invalid {$field}: {$validation['error']}",
                    ], 422);
                }
            }
        }

        // Auto-generate HMAC secret for webhooks only on initial creation
        $existingConfig = AlertChannelConfig::forTenant($request->user()->tenant_id)
            ->forChannel($channel)
            ->first();

        if ($channel === 'webhook' && empty($configData['hmac_secret'])) {
            // Preserve existing secret on updates, only generate on first creation
            if ($existingConfig && !empty($existingConfig->config['hmac_secret'])) {
                $configData['hmac_secret'] = $existingConfig->config['hmac_secret'];
            } else {
                $configData['hmac_secret'] = Str::random(64);
            }
        }

        $channelConfig = AlertChannelConfig::updateOrCreate(
            [
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => null, // Account-level config
                'channel' => $channel,
            ],
            [
                'config' => $configData,
                'is_enabled' => $request->input('is_enabled', true),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $channelConfig->id,
                'channel' => $channelConfig->channel,
                'config' => $channelConfig->safe_config,
                'is_enabled' => $channelConfig->is_enabled,
            ],
        ]);
    }

    /**
     * DELETE /api/v1/alerts/channels/{channel}
     */
    public function destroy(Request $request, string $channel): JsonResponse
    {
        $config = AlertChannelConfig::forTenant($request->user()->tenant_id)
            ->forChannel($channel)
            ->first();

        if (!$config) {
            return response()->json(['success' => false, 'error' => 'Channel config not found.'], 404);
        }

        $config->delete();

        return response()->json(['success' => true]);
    }
}

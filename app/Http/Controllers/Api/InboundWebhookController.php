<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InboundRoutingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives inbound messages from SMS/RCS gateways.
 *
 * Each gateway has its own payload format. This controller normalises
 * the payload and delegates to InboundRoutingService for processing.
 *
 * Route: POST /webhook/inbound/{gateway}
 * No auth middleware — gateways authenticate via signature/token.
 */
class InboundWebhookController extends Controller
{
    private InboundRoutingService $routing;

    public function __construct(InboundRoutingService $routing)
    {
        $this->routing = $routing;
    }

    /**
     * Receive an inbound message from a gateway.
     */
    public function receive(Request $request, string $gateway): JsonResponse
    {
        // Validate gateway signature/token
        if (!$this->validateGatewaySignature($request, $gateway)) {
            Log::warning('Inbound webhook: Invalid signature', [
                'gateway' => $gateway,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Normalise payload from gateway-specific format
        $payload = $this->normalisePayload($request, $gateway);
        if (!$payload) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info('Inbound webhook: Message received', [
            'gateway' => $gateway,
            'from' => $payload['from'],
            'to' => $payload['to'],
            'channel' => $payload['channel'],
        ]);

        // Process through routing service
        $result = $this->routing->processInbound($payload);

        return response()->json([
            'success' => $result['routed'],
            'message' => $result['routed'] ? 'Message processed' : ($result['reason'] ?? 'Routing failed'),
        ]);
    }

    /**
     * Validate the gateway's request signature.
     *
     * Each gateway uses a different authentication mechanism:
     * - HMAC signature in header
     * - Bearer token
     * - IP allowlist
     *
     * TODO: Implement per-gateway signature validation when gateways are built.
     */
    private function validateGatewaySignature(Request $request, string $gateway): bool
    {
        // SECURITY: Fail-closed — reject unknown gateways and missing signatures
        // Each gateway must have a configured secret to accept webhooks
        $secret = config("services.gateways.{$gateway}.webhook_secret");

        if (!$secret) {
            \Illuminate\Support\Facades\Log::warning("Inbound webhook rejected — no webhook_secret configured for gateway", [
                'gateway' => $gateway,
                'ip' => $request->ip(),
            ]);
            return false;
        }

        $signature = $request->header('X-Webhook-Signature') ?? $request->header('X-Signature');

        if (!$signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Normalise gateway-specific payload into a standard format.
     *
     * Standard format:
     * - to: string (E.164)
     * - from: string (E.164)
     * - content: string
     * - channel: string (sms|rcs)
     * - gateway_message_id: string
     * - rcs_payload: ?array
     * - keyword: ?string
     */
    private function normalisePayload(Request $request, string $gateway): ?array
    {
        // Generic format (used during development / for custom gateways)
        // Production gateways will have their own normalisation logic.

        return match ($gateway) {
            'generic' => $this->normaliseGeneric($request),
            default => $this->normaliseGeneric($request),
        };

        // Future gateway-specific normalisers:
        // 'sinch' => $this->normaliseSinch($request),
        // 'twilio' => $this->normaliseTwilio($request),
        // 'vonage' => $this->normaliseVonage($request),
    }

    /**
     * Generic payload normaliser — expects a standard JSON body.
     */
    private function normaliseGeneric(Request $request): ?array
    {
        $validated = $request->validate([
            'to'                 => 'required|string|max:20',
            'from'               => 'required|string|max:20',
            'content'            => 'nullable|string|max:10000',
            'channel'            => 'nullable|string|in:sms,rcs',
            'gateway_message_id' => 'nullable|string|max:255',
            'rcs_payload'        => 'nullable|array',
            'keyword'            => 'nullable|string|max:100',
        ]);

        return [
            'to' => $validated['to'],
            'from' => $validated['from'],
            'content' => $validated['content'] ?? '',
            'channel' => $validated['channel'] ?? 'sms',
            'gateway_message_id' => $validated['gateway_message_id'] ?? null,
            'rcs_payload' => $validated['rcs_payload'] ?? null,
            'keyword' => $validated['keyword'] ?? null,
        ];
    }
}

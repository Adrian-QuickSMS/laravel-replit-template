<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Billing\XeroService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class XeroWebhookController extends Controller
{
    public function __construct(
        private XeroService $xeroService,
    ) {}

    public function handle(Request $request): Response
    {
        // Xero uses Intent to Receive verification
        // On first webhook, Xero sends a hash challenge — respond with 200 and empty body
        $webhookKey = config('services.xero.webhook_key');

        if ($webhookKey) {
            $payload = $request->getContent();
            $signature = $request->header('x-xero-signature');
            $expected = base64_encode(hash_hmac('sha256', $payload, $webhookKey, true));

            if ($signature !== $expected) {
                return response('', 401);
            }
        }

        $data = $request->json()->all();

        // Handle the webhook events
        if (!empty($data['events'])) {
            try {
                $this->xeroService->handlePaymentWebhook($data);
            } catch (\Exception $e) {
                Log::error('Xero webhook processing failed', ['error' => $e->getMessage()]);
            }
        }

        return response('', 200);
    }
}

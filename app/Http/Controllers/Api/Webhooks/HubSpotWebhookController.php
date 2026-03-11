<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Billing\HubSpotPricingSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class HubSpotWebhookController extends Controller
{
    public function __construct(
        private HubSpotPricingSyncService $hubspotSync,
    ) {}

    public function handleDeal(Request $request): Response
    {
        // Verify HubSpot webhook signature (v2)
        $clientSecret = config('services.hubspot.client_secret');
        if ($clientSecret) {
            $signature = $request->header('X-HubSpot-Signature');
            $expectedHash = hash('sha256', $clientSecret . $request->getContent());

            if (!$signature || !hash_equals($expectedHash, $signature)) {
                Log::warning('HubSpot deal webhook signature verification failed', [
                    'ip' => $request->ip(),
                ]);
                return response('Invalid signature', 400);
            }
        } else {
            Log::warning('HubSpot deal webhook received without signature verification — HUBSPOT_CLIENT_SECRET not configured', [
                'ip' => $request->ip(),
            ]);
        }

        // HubSpot sends an array of event objects
        $events = $request->json()->all();

        if (!is_array($events)) {
            return response('OK', 200);
        }

        try {
            $this->hubspotSync->handleDealWebhook($events);
        } catch (\Exception $e) {
            Log::error('HubSpot deal webhook failed', ['error' => $e->getMessage()]);
        }

        return response('OK', 200);
    }
}

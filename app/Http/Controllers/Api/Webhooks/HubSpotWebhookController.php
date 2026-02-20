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

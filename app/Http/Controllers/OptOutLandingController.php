<?php

namespace App\Http\Controllers;

use App\Models\CampaignOptOutUrl;
use App\Services\OptOutService;
use Illuminate\Http\Request;

/**
 * OptOutLandingController — serves the public opt-out landing page.
 *
 * No authentication required (public-facing).
 * Routes: GET /o/{token}, POST /o/{token}/confirm
 */
class OptOutLandingController extends Controller
{
    public function __construct(
        private OptOutService $optOutService,
    ) {}

    public function show(string $token)
    {
        $optOutUrl = CampaignOptOutUrl::where('token', $token)->first();

        if (!$optOutUrl) {
            return view('optout.invalid', ['message' => 'This opt-out link is not valid.']);
        }

        if ($optOutUrl->isExpired()) {
            return view('optout.invalid', ['message' => 'This opt-out link has expired.']);
        }

        if ($optOutUrl->isAlreadyUnsubscribed()) {
            return view('optout.confirmed');
        }

        $optOutUrl->recordClick(request()->ip());

        return view('optout.landing', ['token' => $token]);
    }

    public function confirm(Request $request, string $token)
    {
        $result = $this->optOutService->processUrlOptOut($token, $request->ip());

        if ($result['success']) {
            return view('optout.confirmed');
        }

        return view('optout.invalid', ['message' => $result['message']]);
    }
}

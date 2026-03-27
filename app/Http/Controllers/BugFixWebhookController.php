<?php

namespace App\Http\Controllers;

use App\Services\HubSpotTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BugFixWebhookController extends Controller
{
    private const VALID_EVENTS = ['fix_started', 'pr_opened', 'pr_merged'];

    private const EVENT_STAGE_MAP = [
        'fix_started' => 'in_progress',
        'pr_opened'   => 'fix_ready',
        'pr_merged'   => 'ready_for_testing',
    ];

    private HubSpotTicketService $ticketService;

    public function __construct(HubSpotTicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Handle status updates from GitHub Actions for bug auto-fixes.
     */
    public function handle(Request $request): JsonResponse
    {
        // Always verify webhook signature — no bypass in any environment
        if (!$this->verifySignature($request)) {
            Log::warning('Bug fix webhook: invalid signature', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $validated = $request->validate([
            'event'     => 'required|in:' . implode(',', self::VALID_EVENTS),
            'reference' => 'required|string|regex:/^BUG-\d{8}-[A-Za-z0-9]{6,12}$/',
            'pr_url'    => 'nullable|url|max:500',
            'pr_number' => 'nullable|integer',
            'message'   => 'nullable|string|max:1000',
        ]);

        $event = $validated['event'];
        $reference = $validated['reference'];

        // Idempotency check — prevent duplicate processing from webhook retries
        $idempotencyKey = "bug_webhook:{$reference}:{$event}";
        if (Cache::has($idempotencyKey)) {
            Log::info('Bug fix webhook: duplicate ignored', [
                'event' => $event,
                'reference' => $reference,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Duplicate webhook — already processed',
                'reference' => $reference,
            ]);
        }

        // Mark as processed (TTL 1 hour)
        Cache::put($idempotencyKey, true, 3600);

        Log::info('Bug fix webhook received', [
            'event' => $event,
            'reference' => $reference,
            'pr_url' => $validated['pr_url'] ?? null,
        ]);

        // Find the HubSpot ticket by reference
        $ticketId = $this->ticketService->findTicketByReference($reference);

        if (!$ticketId) {
            Log::warning('Bug fix webhook: ticket not found for reference', [
                'reference' => $reference,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Webhook received, but no matching HubSpot ticket found',
                'reference' => $reference,
            ]);
        }

        // Update ticket stage
        $stage = self::EVENT_STAGE_MAP[$event] ?? null;
        if ($stage) {
            $this->ticketService->updateTicketStage($ticketId, $stage);
        }

        // Add timeline note
        $noteBody = $this->buildNoteBody($event, $validated);
        $this->ticketService->addTicketNote($ticketId, $noteBody);

        Log::info('Bug fix webhook: HubSpot ticket updated', [
            'ticket_id' => $ticketId,
            'reference' => $reference,
            'event' => $event,
            'new_stage' => $stage,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated',
            'reference' => $reference,
            'ticket_id' => $ticketId,
        ]);
    }

    private function verifySignature(Request $request): bool
    {
        $secret = config('services.github.webhook_secret');

        // Always require a configured secret — no bypass in any environment
        if (empty($secret)) {
            Log::error('Bug fix webhook: GITHUB_WEBHOOK_SECRET not configured — rejecting all webhooks');
            return false;
        }

        $signature = $request->header('X-Hub-Signature-256');
        if (empty($signature)) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    private function buildNoteBody(string $event, array $data): string
    {
        $reference = $data['reference'];

        switch ($event) {
            case 'fix_started':
                return "**Claude Code Auto-Fix Started**\n\n"
                    . "Claude Code is analysing bug report {$reference} and attempting an automated fix.\n\n"
                    . ($data['message'] ?? '');

            case 'pr_opened':
                $prUrl = $data['pr_url'] ?? '';
                $prNum = $data['pr_number'] ?? '';
                return "**Pull Request Opened**\n\n"
                    . "Claude Code has opened PR #{$prNum} with a proposed fix for {$reference}.\n\n"
                    . "**PR URL:** {$prUrl}\n\n"
                    . "This is a **draft PR** — human review required before merging.\n\n"
                    . ($data['message'] ?? '');

            case 'pr_merged':
                $prUrl = $data['pr_url'] ?? '';
                $prNum = $data['pr_number'] ?? '';
                return "**Fix Merged — Ready for Testing**\n\n"
                    . "PR #{$prNum} for {$reference} has been merged.\n\n"
                    . "**PR URL:** {$prUrl}\n\n"
                    . "The fix is now deployed and ready for verification.\n\n"
                    . ($data['message'] ?? '');

            default:
                return "Bug fix status update for {$reference}: {$event}\n\n"
                    . ($data['message'] ?? '');
        }
    }
}

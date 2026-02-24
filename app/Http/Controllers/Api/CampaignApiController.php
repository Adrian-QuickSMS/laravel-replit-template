<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCampaignBatch;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Campaign\CampaignService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Campaign API Controller
 *
 * JSON API for the Send Message module frontend:
 * - Campaign CRUD (create, read, update, delete)
 * - Recipient resolution + preview
 * - Cost estimation
 * - Send operations (send now, schedule, pause, resume, cancel)
 * - Campaign cloning
 * - Recipient listing
 *
 * SECURITY: All methods rely on tenant isolation via Campaign global scope.
 * Routes must be under customer.auth middleware.
 */
class CampaignApiController extends Controller
{
    public function __construct(
        private CampaignService $campaignService,
    ) {}

    private function tenantId(): string
    {
        return session('customer_tenant_id');
    }

    // =====================================================
    // CAMPAIGN CRUD
    // =====================================================

    /**
     * List campaigns with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::query();

        if ($request->filled('status')) {
            $query->ofStatus($request->input('status'));
        }
        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $campaigns = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $campaigns->getCollection()->map(fn($c) => $c->toListArray()),
            'total' => $campaigns->total(),
            'per_page' => $campaigns->perPage(),
            'current_page' => $campaigns->currentPage(),
            'last_page' => $campaigns->lastPage(),
        ]);
    }

    /**
     * Get a single campaign with full details.
     */
    public function show(string $id): JsonResponse
    {
        $campaign = Campaign::with(['messageTemplate', 'senderId', 'rcsAgent'])->find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $data = $campaign->toPortalArray();

        // Include template info if linked
        if ($campaign->messageTemplate) {
            $data['template'] = $campaign->messageTemplate->toPortalArray();
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Create a new campaign (draft).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:sms,rcs_basic,rcs_single',
            'message_template_id' => 'nullable|uuid',
            'message_content' => 'nullable|string|max:10000',
            'rcs_content' => 'nullable|array',
            'sender_id_id' => 'nullable|integer',
            'rcs_agent_id' => 'nullable|integer',
            'recipient_sources' => 'nullable|array',
            'recipient_sources.*.type' => 'required_with:recipient_sources|string|in:list,tag,individual,manual,csv',
            'scheduled_at' => 'nullable|date|after:now',
            'timezone' => 'nullable|string|max:50',
            'send_rate' => 'nullable|integer|min:0|max:500',
            'batch_size' => 'nullable|integer|min:100|max:10000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
        ]);

        $validated['created_by'] = session('customer_email', session('customer_user_id'));

        $campaign = $this->campaignService->create($this->tenantId(), $validated);

        return response()->json(['data' => $campaign->toPortalArray()], 201);
    }

    /**
     * Update a draft campaign.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        if (!$campaign->isEditable()) {
            return response()->json([
                'status' => 'error',
                'message' => "Campaign cannot be edited in '{$campaign->status}' status.",
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'sometimes|string|in:sms,rcs_basic,rcs_single',
            'message_template_id' => 'nullable|uuid',
            'message_content' => 'nullable|string|max:10000',
            'rcs_content' => 'nullable|array',
            'sender_id_id' => 'nullable|integer',
            'rcs_agent_id' => 'nullable|integer',
            'recipient_sources' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
            'timezone' => 'nullable|string|max:50',
            'send_rate' => 'nullable|integer|min:0|max:500',
            'batch_size' => 'nullable|integer|min:100|max:10000',
            'tags' => 'nullable|array',
        ]);

        $validated['updated_by'] = session('customer_email', session('customer_user_id'));

        $campaign = $this->campaignService->update($campaign, $validated);

        return response()->json(['data' => $campaign->toPortalArray()]);
    }

    /**
     * Delete a campaign (soft delete, draft/terminal only).
     */
    public function destroy(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        try {
            $this->campaignService->delete($campaign);
            return response()->json(['success' => true, 'message' => 'Campaign deleted']);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    // =====================================================
    // TEMPLATE
    // =====================================================

    /**
     * Apply a message template to a campaign.
     */
    public function applyTemplate(Request $request, string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $request->validate(['template_id' => 'required|uuid']);

        try {
            $campaign = $this->campaignService->applyTemplate($campaign, $request->input('template_id'));
            return response()->json(['data' => $campaign->toPortalArray()]);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    // =====================================================
    // RECIPIENTS
    // =====================================================

    /**
     * Preview recipient counts without persisting.
     */
    public function previewRecipients(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $preview = $this->campaignService->previewRecipients($campaign);

        return response()->json(['data' => $preview]);
    }

    /**
     * Resolve and persist recipients for a campaign.
     */
    public function resolveRecipients(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        try {
            $result = $this->campaignService->resolveRecipients($campaign);
            return response()->json(['data' => $result->toArray()]);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * List recipients for a campaign with filtering.
     */
    public function recipients(Request $request, string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $query = CampaignRecipient::where('campaign_id', $id);

        if ($request->filled('status')) {
            $query->ofStatus($request->input('status'));
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $recipients = $query->orderBy('batch_number')->orderBy('id')->paginate($perPage);

        return response()->json([
            'data' => $recipients->getCollection()->map(fn($r) => $r->toPortalArray()),
            'total' => $recipients->total(),
            'per_page' => $recipients->perPage(),
            'current_page' => $recipients->currentPage(),
            'last_page' => $recipients->lastPage(),
        ]);
    }

    // =====================================================
    // COST ESTIMATION
    // =====================================================

    /**
     * Get cost estimate for a campaign.
     */
    public function estimateCost(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $estimate = $this->campaignService->estimateCost($campaign);

        return response()->json(['data' => $estimate->toArray()]);
    }

    // =====================================================
    // VALIDATION
    // =====================================================

    /**
     * Validate a campaign is ready to send (dry run).
     */
    public function validate_(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $errors = $this->campaignService->validateForSend($campaign);

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors,
        ]);
    }

    // =====================================================
    // SEND OPERATIONS
    // =====================================================

    /**
     * Send a campaign immediately.
     */
    public function sendNow(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        try {
            $result = $this->campaignService->sendNow($campaign);

            // Dispatch batch jobs
            $this->dispatchBatchJobs($campaign);

            return response()->json([
                'success' => true,
                'message' => 'Campaign queued for sending',
                'data' => $result->toArray(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Schedule a campaign for future send.
     */
    public function schedule(Request $request, string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'timezone' => 'nullable|string|max:50',
        ]);

        try {
            $campaign = $this->campaignService->schedule(
                $campaign,
                $request->input('scheduled_at'),
                $request->input('timezone')
            );

            return response()->json([
                'success' => true,
                'message' => 'Campaign scheduled',
                'data' => $campaign->toPortalArray(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Pause a sending campaign.
     */
    public function pause(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        try {
            $this->campaignService->pause($campaign);
            return response()->json(['success' => true, 'message' => 'Campaign paused']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Resume a paused campaign.
     */
    public function resume(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        try {
            $this->campaignService->resume($campaign);

            // Re-dispatch batch jobs for remaining recipients
            $this->dispatchBatchJobs($campaign);

            return response()->json(['success' => true, 'message' => 'Campaign resumed']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel a campaign.
     */
    public function cancel(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        try {
            $this->campaignService->cancel($campaign);
            return response()->json(['success' => true, 'message' => 'Campaign cancelled']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    // =====================================================
    // CLONE
    // =====================================================

    /**
     * Clone a campaign as a new draft.
     */
    public function clone(Request $request, string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $newName = $request->input('name');
        $clone = $this->campaignService->clone($campaign, $newName);

        return response()->json([
            'success' => true,
            'data' => $clone->toPortalArray(),
        ], 201);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Dispatch ProcessCampaignBatch jobs for all pending batches.
     */
    private function dispatchBatchJobs(Campaign $campaign): void
    {
        $maxBatch = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->whereIn('status', [
                CampaignRecipient::STATUS_PENDING,
                CampaignRecipient::STATUS_QUEUED,
            ])
            ->max('batch_number') ?? 0;

        for ($batch = 0; $batch <= $maxBatch; $batch++) {
            ProcessCampaignBatch::dispatch($campaign->id, $batch);
        }

        Log::info('[CampaignApiController] Batch jobs dispatched', [
            'campaign_id' => $campaign->id,
            'total_batches' => $maxBatch + 1,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCampaignBatch;
use App\Models\Campaign;
use App\Models\CampaignOptOutUrl;
use App\Models\CampaignRecipient;
use App\Services\Campaign\CampaignService;
use App\Services\OptOutService;
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
        private OptOutService $optOutService,
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
            'recipient_sources' => 'nullable|array|max:50',
            'recipient_sources.*.type' => 'required_with:recipient_sources|string|in:list,tag,individual,manual,csv',
            'recipient_sources.*.id' => 'nullable|uuid',
            'recipient_sources.*.contact_ids' => 'nullable|array|max:100000',
            'recipient_sources.*.contact_ids.*' => 'uuid',
            'recipient_sources.*.numbers' => 'nullable|array|max:100000',
            'recipient_sources.*.numbers.*' => 'string|max:30',
            'recipient_sources.*.data' => 'nullable|array|max:1000000',
            'recipient_sources.*.data.*.mobile_number' => 'nullable|string|max:30',
            'recipient_sources.*.data.*.phone' => 'nullable|string|max:30',
            'recipient_sources.*.data.*.mobile' => 'nullable|string|max:30',
            'scheduled_at' => 'nullable|date|after:now',
            'timezone' => 'nullable|string|max:50',
            'send_rate' => 'nullable|integer|min:0|max:500',
            'batch_size' => 'nullable|integer|min:100|max:10000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            // Opt-out configuration
            'opt_out_enabled' => 'nullable|boolean',
            'opt_out_method' => 'nullable|string|in:reply,url,both',
            'opt_out_number_id' => 'nullable|uuid',
            'opt_out_keyword' => 'nullable|string|min:4|max:10|regex:/^[A-Za-z0-9]+$/',
            'opt_out_text' => 'nullable|string|max:500',
            'opt_out_list_id' => 'nullable|uuid',
            'opt_out_url_enabled' => 'nullable|boolean',
        ]);

        // Validate opt-out keyword if provided
        if (!empty($validated['opt_out_keyword']) && !empty($validated['opt_out_number_id'])) {
            try {
                $this->optOutService->validateOptOutKeyword(
                    $validated['opt_out_keyword'],
                    $validated['opt_out_number_id'],
                    $this->tenantId()
                );
            } catch (\RuntimeException $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
        }

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
            'recipient_sources' => 'nullable|array|max:50',
            'recipient_sources.*.type' => 'required_with:recipient_sources|string|in:list,tag,individual,manual,csv',
            'recipient_sources.*.id' => 'nullable|uuid',
            'recipient_sources.*.contact_ids' => 'nullable|array|max:100000',
            'recipient_sources.*.contact_ids.*' => 'uuid',
            'recipient_sources.*.numbers' => 'nullable|array|max:100000',
            'recipient_sources.*.numbers.*' => 'string|max:30',
            'scheduled_at' => 'nullable|date|after:now',
            'timezone' => 'nullable|string|max:50',
            'send_rate' => 'nullable|integer|min:0|max:500',
            'batch_size' => 'nullable|integer|min:100|max:10000',
            'tags' => 'nullable|array',
            // Opt-out configuration
            'opt_out_enabled' => 'nullable|boolean',
            'opt_out_method' => 'nullable|string|in:reply,url,both',
            'opt_out_number_id' => 'nullable|uuid',
            'opt_out_keyword' => 'nullable|string|min:4|max:10|regex:/^[A-Za-z0-9]+$/',
            'opt_out_text' => 'nullable|string|max:500',
            'opt_out_list_id' => 'nullable|uuid',
            'opt_out_url_enabled' => 'nullable|boolean',
        ]);

        // Validate opt-out keyword if provided
        $keyword = $validated['opt_out_keyword'] ?? $campaign->opt_out_keyword;
        $numberId = $validated['opt_out_number_id'] ?? $campaign->opt_out_number_id;
        if ($keyword && $numberId) {
            try {
                $this->optOutService->validateOptOutKeyword(
                    $keyword,
                    $numberId,
                    $this->tenantId(),
                    $campaign->id
                );
            } catch (\RuntimeException $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
        }

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
    // CAMPAIGN PREPARATION (resolve recipients + content + segments)
    // =====================================================

    /**
     * Prepare a campaign for the confirm page.
     *
     * Resolves recipients synchronously, then dispatches an async job
     * to resolve merge fields and calculate per-recipient segments.
     * Frontend should poll preparationStatus() until ready.
     */
    public function prepare(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        try {
            $resolverResult = $this->campaignService->prepareCampaign($campaign);

            return response()->json([
                'success' => true,
                'message' => 'Campaign preparation started',
                'data' => [
                    'resolver_result' => $resolverResult->toArray(),
                    'preparation_status' => $campaign->fresh()->preparation_status,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Poll campaign preparation status.
     *
     * Returns progress percentage while preparing. Once ready, includes
     * the accurate cost estimate and segment distribution statistics.
     */
    public function preparationStatus(string $id): JsonResponse
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
        }

        $result = $this->campaignService->getPreparationStatus($campaign);

        // Include cost estimate once preparation is complete
        if ($campaign->preparation_status === 'ready') {
            $estimate = $this->campaignService->estimateCost($campaign);
            $result['cost_estimate'] = $estimate->toArray();
        }

        return response()->json(['data' => $result]);
    }

    /**
     * Get field length statistics from the contact database.
     *
     * Used on the send-message form for live segment range estimates
     * before full recipient resolution. Returns average and max character
     * lengths per merge field, optionally scoped to selected sources.
     */
    public function fieldStatistics(Request $request): JsonResponse
    {
        $accountId = $this->tenantId();

        $listIds = $request->input('list_ids', []);
        $tagIds = $request->input('tag_ids', []);
        $contactIds = $request->input('contact_ids', []);

        $query = DB::table('contacts')
            ->where('account_id', $accountId)
            ->whereNull('deleted_at');

        // Scope to selected sources if provided
        if (!empty($listIds) || !empty($tagIds) || !empty($contactIds)) {
            $query->where(function ($q) use ($listIds, $tagIds, $contactIds) {
                if (!empty($listIds)) {
                    $q->orWhereIn('id', function ($sub) use ($listIds) {
                        $sub->select('contact_id')
                            ->from('contact_list_member')
                            ->whereIn('list_id', $listIds);
                    });
                }
                if (!empty($tagIds)) {
                    $q->orWhereIn('id', function ($sub) use ($tagIds) {
                        $sub->select('contact_id')
                            ->from('contact_tag')
                            ->whereIn('tag_id', $tagIds);
                    });
                }
                if (!empty($contactIds)) {
                    $q->orWhereIn('id', $contactIds);
                }
            });
        }

        $stats = $query->select([
            DB::raw('ROUND(AVG(LENGTH(COALESCE(first_name, \'\'))), 1) as avg_first_name_len'),
            DB::raw('MAX(LENGTH(COALESCE(first_name, \'\'))) as max_first_name_len'),
            DB::raw('ROUND(AVG(LENGTH(COALESCE(last_name, \'\'))), 1) as avg_last_name_len'),
            DB::raw('MAX(LENGTH(COALESCE(last_name, \'\'))) as max_last_name_len'),
            DB::raw('ROUND(AVG(LENGTH(COALESCE(email, \'\'))), 1) as avg_email_len'),
            DB::raw('MAX(LENGTH(COALESCE(email, \'\'))) as max_email_len'),
            DB::raw('COUNT(*) as total_contacts'),
        ])->first();

        return response()->json(['data' => $stats]);
    }

    // =====================================================
    // COST ESTIMATION
    // =====================================================

    /**
     * Get cost estimate for a campaign.
     *
     * If content has been resolved (via prepare), returns an accurate
     * cost based on per-recipient segment counts. Otherwise returns
     * a flat estimate using the campaign-level segment count.
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

        $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $newName = $request->input('name');
        $clone = $this->campaignService->clone($campaign, $newName);

        return response()->json([
            'success' => true,
            'data' => $clone->toPortalArray(),
        ], 201);
    }

    // =====================================================
    // OPT-OUT ENDPOINTS
    // =====================================================

    /**
     * Get available numbers for opt-out reply selector (Red Circle).
     * Returns VMNs, shortcodes, and keywords usable by the current user.
     */
    public function optOutNumbers(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $numbers = $this->optOutService->getAvailableOptOutNumbers($this->tenantId(), $user);

        return response()->json(['data' => $numbers]);
    }

    /**
     * Validate an opt-out keyword for a specific number.
     * Returns whether the keyword is available for use.
     */
    public function validateOptOutKeyword(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:4|max:10|regex:/^[A-Za-z0-9]+$/',
            'number_id' => 'required|uuid',
            'campaign_id' => 'nullable|uuid',
        ]);

        try {
            $this->optOutService->validateOptOutKeyword(
                $request->input('keyword'),
                $request->input('number_id'),
                $this->tenantId(),
                $request->input('campaign_id')
            );

            return response()->json([
                'valid' => true,
                'keyword' => strtoupper($request->input('keyword')),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'valid' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available keywords for a shared shortcode.
     * Returns purchased keywords not in use by in-flight campaigns.
     */
    public function availableKeywords(string $numberId): JsonResponse
    {
        $keywords = $this->optOutService->getAvailableKeywords(
            $numberId,
            $this->tenantId()
        );

        return response()->json(['data' => $keywords]);
    }

    /**
     * Generate suggested opt-out text for a keyword + number combination.
     */
    public function suggestOptOutText(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:4|max:10',
            'number_id' => 'required|uuid',
        ]);

        $number = \App\Models\PurchasedNumber::findOrFail($request->input('number_id'));

        $text = $this->optOutService->generateOptOutText(
            strtoupper($request->input('keyword')),
            $number->number
        );

        // Calculate character impact (for segment estimation)
        $charCount = strlen($text);
        $optOutUrlCharCount = CampaignOptOutUrl::getUrlCharCount();

        return response()->json([
            'text' => $text,
            'char_count' => $charCount,
            'opt_out_url_char_count' => $optOutUrlCharCount,
            'opt_out_url_preview' => CampaignOptOutUrl::getFixedLengthUrl(),
        ]);
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

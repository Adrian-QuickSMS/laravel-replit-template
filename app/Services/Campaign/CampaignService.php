<?php

namespace App\Services\Campaign;

use App\Jobs\ResolveRecipientContentJob;
use App\Models\Account;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\MessageTemplate;
use App\Models\RcsAgent;
use App\Models\SenderId;
use App\Services\Admin\MessageEnforcementService;
use App\Services\RcsContentValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * CampaignService — orchestrates the full campaign lifecycle.
 *
 * Coordinates:
 * - RecipientResolverService (expand, dedup, validate, opt-out)
 * - BillingPreflightService (estimate, check, reserve)
 * - MessageEnforcementService (content/sender/URL scanning)
 * - Campaign state machine (draft -> scheduled/sending -> completed)
 *
 * Does NOT handle actual message dispatch (that's DeliveryService + queue jobs).
 */
class CampaignService
{
    public function __construct(
        private RecipientResolverService $recipientResolver,
        private BillingPreflightService $billingPreflight,
        private MessageEnforcementService $enforcement,
        private RcsContentValidator $rcsValidator,
    ) {}

    // =====================================================
    // CREATE / UPDATE
    // =====================================================

    /**
     * Create a new campaign in draft status.
     *
     * @param string $accountId Tenant account ID
     * @param array $data Campaign data from frontend
     * @return Campaign
     */
    public function create(string $accountId, array $data): Campaign
    {
        $campaign = Campaign::create([
            'account_id' => $accountId,
            'sub_account_id' => $data['sub_account_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'status' => Campaign::STATUS_DRAFT,
            'message_template_id' => $data['message_template_id'] ?? null,
            'message_content' => $data['message_content'] ?? null,
            'rcs_content' => $data['rcs_content'] ?? null,
            'encoding' => $data['encoding'] ?? null,
            'segment_count' => $data['segment_count'] ?? 1,
            'sender_id_id' => $data['sender_id_id'] ?? null,
            'rcs_agent_id' => $data['rcs_agent_id'] ?? null,
            'recipient_sources' => $data['recipient_sources'] ?? [],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'timezone' => $data['timezone'] ?? null,
            'send_rate' => $data['send_rate'] ?? 0,
            'batch_size' => $data['batch_size'] ?? Campaign::DEFAULT_BATCH_SIZE,
            'tags' => $data['tags'] ?? [],
            'metadata' => $data['metadata'] ?? [],
            'created_by' => $data['created_by'] ?? null,
            // Opt-out configuration
            'opt_out_enabled' => $data['opt_out_enabled'] ?? false,
            'opt_out_method' => $data['opt_out_method'] ?? null,
            'opt_out_number_id' => $data['opt_out_number_id'] ?? null,
            'opt_out_keyword' => $data['opt_out_keyword'] ?? null,
            'opt_out_text' => $data['opt_out_text'] ?? null,
            'opt_out_list_id' => $data['opt_out_list_id'] ?? null,
            'opt_out_url_enabled' => $data['opt_out_url_enabled'] ?? false,
        ]);

        // Auto-calculate encoding/segments if SMS content provided
        if ($campaign->message_content && in_array($campaign->type, [Campaign::TYPE_SMS, Campaign::TYPE_RCS_BASIC])) {
            $campaign->encoding = MessageTemplate::detectEncoding($campaign->message_content);
            $campaign->segment_count = MessageTemplate::calculateSegments($campaign->message_content, $campaign->encoding);
            $campaign->save();
        }

        // Validate RCS content structure on save
        if ($campaign->rcs_content && in_array($campaign->type, [Campaign::TYPE_RCS_SINGLE, Campaign::TYPE_RCS_CAROUSEL])) {
            $rcsErrors = $this->rcsValidator->validateStructure($campaign->rcs_content, $campaign->type);
            if (!empty($rcsErrors)) {
                throw ValidationException::withMessages(['rcs_content' => $rcsErrors]);
            }
        }

        Log::info('[CampaignService] Campaign created', [
            'campaign_id' => $campaign->id,
            'account_id' => $accountId,
            'type' => $campaign->type,
        ]);

        return $campaign;
    }

    /**
     * Update a draft campaign.
     *
     * @throws \RuntimeException if campaign is not in draft status
     */
    public function update(Campaign $campaign, array $data): Campaign
    {
        if (!$campaign->isEditable()) {
            throw new \RuntimeException("Campaign cannot be updated in '{$campaign->status}' status.");
        }

        $campaign->fill($data);

        // Recalculate encoding/segments if message content changed
        if ($campaign->isDirty('message_content') && $campaign->message_content) {
            if (in_array($campaign->type, [Campaign::TYPE_SMS, Campaign::TYPE_RCS_BASIC])) {
                $campaign->encoding = MessageTemplate::detectEncoding($campaign->message_content);
                $campaign->segment_count = MessageTemplate::calculateSegments($campaign->message_content, $campaign->encoding);
            }

            // Invalidate resolved content — per-recipient segments are now stale
            $campaign->content_resolved_at = null;
            $campaign->preparation_status = null;
            $campaign->preparation_progress = 0;
            $campaign->preparation_error = null;

            DB::table('campaign_recipients')
                ->where('campaign_id', $campaign->id)
                ->update([
                    'resolved_content' => null,
                    'segments' => $campaign->segment_count ?: 1,
                    'encoding' => null,
                ]);
        }

        // Validate RCS content structure if changed
        if ($campaign->isDirty('rcs_content') && $campaign->rcs_content && in_array($campaign->type, [Campaign::TYPE_RCS_SINGLE, Campaign::TYPE_RCS_CAROUSEL])) {
            $rcsErrors = $this->rcsValidator->validateStructure($campaign->rcs_content, $campaign->type);
            if (!empty($rcsErrors)) {
                throw ValidationException::withMessages(['rcs_content' => $rcsErrors]);
            }
        }

        $campaign->updated_by = $data['updated_by'] ?? $campaign->updated_by;
        $campaign->save();

        return $campaign;
    }

    /**
     * Apply a message template to a campaign.
     * Copies template content into the campaign but keeps them independent after.
     */
    public function applyTemplate(Campaign $campaign, string $templateId): Campaign
    {
        if (!$campaign->isEditable()) {
            throw new \RuntimeException("Cannot apply template to campaign in '{$campaign->status}' status.");
        }

        $template = MessageTemplate::findOrFail($templateId);

        $campaign->update([
            'message_template_id' => $template->id,
            'message_content' => $template->content,
            'rcs_content' => $template->rcs_content,
            'encoding' => $template->encoding,
            'segment_count' => $template->segment_count,
            // Invalidate any previously resolved content — template content has changed
            'content_resolved_at' => null,
            'preparation_status' => null,
            'preparation_progress' => 0,
            'preparation_error' => null,
        ]);

        // Clear stale per-recipient resolved content
        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->update([
                'resolved_content' => null,
                'segments' => $template->segment_count ?: 1,
                'encoding' => null,
            ]);

        return $campaign;
    }

    // =====================================================
    // RECIPIENT RESOLUTION
    // =====================================================

    /**
     * Preview recipient resolution without persisting.
     * Returns estimated counts for the UI.
     */
    public function previewRecipients(Campaign $campaign): array
    {
        return $this->recipientResolver->preview(
            $campaign->recipient_sources ?? [],
            $campaign->account_id
        );
    }

    /**
     * Resolve and persist campaign recipients.
     * Must be called before sending.
     */
    public function resolveRecipients(Campaign $campaign): ResolverResult
    {
        if (!$campaign->isDraft()) {
            throw new \RuntimeException("Recipients can only be resolved for draft campaigns.");
        }

        // Clear any existing recipients (in case of re-resolution)
        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->delete();

        return $this->recipientResolver->resolve($campaign);
    }

    // =====================================================
    // CAMPAIGN PREPARATION (recipient resolution + content resolution)
    // =====================================================

    /**
     * Prepare a campaign for the confirm page.
     *
     * 1. Resolve recipients synchronously (contact list/tag expansion, dedup, opt-out filtering)
     * 2. Dispatch async content resolution job (merge field substitution + per-recipient segment calculation)
     *
     * The frontend should poll preparationStatus() until preparation_status === 'ready',
     * then call estimateCost() for the accurate total.
     *
     * @throws \RuntimeException if campaign is not in draft status
     */
    public function prepareCampaign(Campaign $campaign): ResolverResult
    {
        if (!$campaign->isDraft()) {
            throw new \RuntimeException("Campaign must be in draft status to prepare.");
        }

        // Clear any existing recipients (supports re-preparation after message edits)
        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->delete();

        // Reset preparation state
        $campaign->update([
            'content_resolved_at' => null,
            'preparation_status' => 'preparing',
            'preparation_progress' => 0,
            'preparation_error' => null,
        ]);

        // Step 1: Resolve recipients synchronously
        // (expands lists/tags, deduplicates, filters opt-outs, inserts campaign_recipients)
        $resolverResult = $this->recipientResolver->resolve($campaign);

        if ($resolverResult->totalCreated === 0) {
            $campaign->update([
                'preparation_status' => 'ready',
                'preparation_progress' => 100,
                'content_resolved_at' => now(),
            ]);
            return $resolverResult;
        }

        // Step 2: Dispatch async content resolution
        // (merge fields + per-recipient encoding detection + segment calculation)
        ResolveRecipientContentJob::dispatch($campaign->id);

        Log::info('[CampaignService] Campaign preparation started', [
            'campaign_id' => $campaign->id,
            'recipients_resolved' => $resolverResult->totalCreated,
        ]);

        return $resolverResult;
    }

    /**
     * Get the current preparation status and segment statistics.
     * Called by the frontend to poll until preparation is ready.
     */
    public function getPreparationStatus(Campaign $campaign): array
    {
        $result = [
            'preparation_status' => $campaign->preparation_status,
            'preparation_progress' => $campaign->preparation_progress ?? 0,
        ];

        if ($campaign->preparation_status === 'ready') {
            // Include segment distribution statistics
            $segmentStats = DB::table('campaign_recipients')
                ->where('campaign_id', $campaign->id)
                ->where('status', CampaignRecipient::STATUS_PENDING)
                ->select([
                    DB::raw('MIN(segments) as min_segments'),
                    DB::raw('MAX(segments) as max_segments'),
                    DB::raw('ROUND(AVG(segments)::numeric, 2) as avg_segments'),
                    DB::raw("COUNT(*) FILTER (WHERE encoding = 'unicode') as unicode_count"),
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw('SUM(segments) as total_segments'),
                ])
                ->first();

            $result['segment_stats'] = [
                'min_segments' => (int) ($segmentStats->min_segments ?? 1),
                'max_segments' => (int) ($segmentStats->max_segments ?? 1),
                'avg_segments' => (float) ($segmentStats->avg_segments ?? 1),
                'unicode_count' => (int) ($segmentStats->unicode_count ?? 0),
                'total_count' => (int) ($segmentStats->total_count ?? 0),
                'total_segments' => (int) ($segmentStats->total_segments ?? 0),
            ];
        } elseif ($campaign->preparation_status === 'failed') {
            $result['error'] = $campaign->preparation_error;
        }

        return $result;
    }

    // =====================================================
    // COST ESTIMATION
    // =====================================================

    /**
     * Get a cost estimate for the campaign.
     * Requires recipients to be resolved first.
     *
     * If content has been resolved (per-recipient segments calculated),
     * returns an accurate cost based on actual segment distribution.
     * Otherwise falls back to flat segment_count estimate.
     */
    public function estimateCost(Campaign $campaign): CostEstimate
    {
        $account = Account::findOrFail($campaign->account_id);

        // Accurate path: content resolved, use per-recipient segment data
        if ($campaign->content_resolved_at) {
            return $this->estimateCostFromResolvedRecipients($account, $campaign);
        }

        // Fallback: flat estimate using campaign-level segment_count
        $countryBreakdown = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->select('country_iso', DB::raw('COUNT(*) as count'))
            ->groupBy('country_iso')
            ->pluck('count', 'country_iso')
            ->toArray();

        return $this->billingPreflight->estimateCost(
            $account,
            $campaign->type,
            $countryBreakdown,
            $campaign->segment_count ?: 1
        );
    }

    /**
     * Calculate cost using actual per-recipient segment data.
     *
     * Groups recipients by (country_iso, segments) so each group is priced
     * independently — a recipient with 2 segments costs exactly double
     * a recipient with 1 segment in the same country.
     */
    private function estimateCostFromResolvedRecipients(Account $account, Campaign $campaign): CostEstimate
    {
        $breakdown = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->select('country_iso', 'segments', DB::raw('COUNT(*) as recipient_count'))
            ->groupBy('country_iso', 'segments')
            ->get();

        return $this->billingPreflight->estimateCostPerSegmentGroup(
            $account,
            $campaign->type,
            $breakdown
        );
    }

    // =====================================================
    // VALIDATION
    // =====================================================

    /**
     * Validate a campaign is ready to send.
     * Checks: content, sender, recipients, enforcement rules.
     *
     * @return array Validation errors (empty = valid)
     */
    public function validateForSend(Campaign $campaign): array
    {
        $errors = [];

        // 1. Message content required
        if ($campaign->isSms() || $campaign->type === Campaign::TYPE_RCS_BASIC) {
            if (empty($campaign->message_content)) {
                $errors[] = 'Message content is required.';
            }
        } elseif (in_array($campaign->type, [Campaign::TYPE_RCS_SINGLE, Campaign::TYPE_RCS_CAROUSEL])) {
            if (empty($campaign->rcs_content)) {
                $errors[] = 'RCS rich content is required.';
            } else {
                // Full structural validation including asset finalization checks
                $rcsErrors = $this->rcsValidator->validateForSend($campaign->rcs_content, $campaign->type);
                $errors = array_merge($errors, $rcsErrors);
            }
        }

        // 2. Sender required and must be approved
        if ($campaign->isSms()) {
            if (!$campaign->sender_id_id) {
                $errors[] = 'An approved Sender ID is required for SMS campaigns.';
            } else {
                $sender = SenderId::withoutGlobalScope('tenant')->find($campaign->sender_id_id);
                if (!$sender || $sender->status !== 'approved') {
                    $errors[] = 'The selected Sender ID is not approved.';
                }
                if ($sender && $sender->account_id !== $campaign->account_id) {
                    $errors[] = 'The selected Sender ID does not belong to this account.';
                }
            }
        } else {
            // RCS: need approved agent
            if (!$campaign->rcs_agent_id) {
                $errors[] = 'An approved RCS Agent is required for RCS campaigns.';
            } else {
                $agent = RcsAgent::withoutGlobalScope('tenant')->find($campaign->rcs_agent_id);
                if (!$agent || $agent->status !== 'approved') {
                    $errors[] = 'The selected RCS Agent is not approved.';
                }
                if ($agent && $agent->account_id !== $campaign->account_id) {
                    $errors[] = 'The selected RCS Agent does not belong to this account.';
                }
            }
        }

        // 3. Recipients must be resolved
        $pendingCount = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->count();

        if ($pendingCount === 0) {
            $errors[] = 'No valid recipients found. Please resolve recipients first.';
        }

        // 4. Content enforcement (sender ID, content, URLs)
        if ($campaign->message_content && $this->enforcement) {
            $senderValue = null;
            if ($campaign->isSms() && $campaign->sender_id_id) {
                $sender = SenderId::withoutGlobalScope('tenant')->find($campaign->sender_id_id);
                $senderValue = $sender?->sender_id_value;
            }

            // Check sender ID enforcement
            if ($senderValue) {
                $senderResult = $this->enforcement->testEnforcement('senderid', $senderValue);
                if ($senderResult['result'] === 'block') {
                    $errors[] = "Sender ID '{$senderValue}' is blocked by enforcement rules: "
                        . ($senderResult['matchedRule']['name'] ?? 'unknown rule');
                } elseif ($senderResult['result'] === 'quarantine') {
                    $errors[] = "Sender ID '{$senderValue}' requires manual review (quarantined by enforcement rules).";
                }
            }

            // Check content enforcement
            $contentResult = $this->enforcement->testEnforcement('content', $campaign->message_content);
            if ($contentResult['result'] === 'block') {
                $errors[] = "Message content blocked by enforcement rules: "
                    . ($contentResult['matchedRule']['name'] ?? 'unknown rule');
            } elseif ($contentResult['result'] === 'quarantine') {
                $errors[] = "Message content requires manual review (quarantined by enforcement rules).";
            }

            // Check URL enforcement
            if (preg_match_all('/https?:\/\/[^\s]+/i', $campaign->message_content, $urlMatches)) {
                foreach ($urlMatches[0] as $url) {
                    $urlResult = $this->enforcement->testEnforcement('url', $url);
                    if ($urlResult['result'] === 'block') {
                        $errors[] = "URL '{$url}' is blocked by enforcement rules.";
                        break; // One blocked URL is enough to report
                    }
                }
            }
        }

        return $errors;
    }

    // =====================================================
    // SEND OPERATIONS
    // =====================================================

    /**
     * Send a campaign immediately.
     *
     * Full pipeline: validate -> billing preflight -> resolve content -> queue for sending.
     *
     * @throws ValidationException if campaign fails validation
     * @throws \App\Exceptions\Billing\InsufficientBalanceException
     * @throws PreflightFailedException
     */
    public function sendNow(Campaign $campaign): PreflightResult
    {
        // Must be draft
        if (!$campaign->isDraft()) {
            throw new \RuntimeException("Campaign must be in draft status to send. Current: {$campaign->status}");
        }

        // Validate
        $errors = $this->validateForSend($campaign);
        if (!empty($errors)) {
            throw ValidationException::withMessages(['campaign' => $errors]);
        }

        // Billing preflight (estimate, balance check, reserve funds)
        $preflightResult = $this->billingPreflight->runPreflight($campaign);

        // Resolve per-recipient content (merge fields)
        $this->resolveRecipientContent($campaign);

        // Transition to queued (ready for queue workers to pick up)
        $campaign->transitionTo(Campaign::STATUS_QUEUED);

        Log::info('[CampaignService] Campaign queued for immediate send', [
            'campaign_id' => $campaign->id,
            'estimated_cost' => $preflightResult->estimatedCost,
            'reservation_id' => $preflightResult->reservationId,
        ]);

        return $preflightResult;
    }

    /**
     * Schedule a campaign for future send.
     */
    public function schedule(Campaign $campaign, string $scheduledAt, ?string $timezone = null): Campaign
    {
        if (!$campaign->isDraft()) {
            throw new \RuntimeException("Campaign must be in draft status to schedule.");
        }

        // Validate
        $errors = $this->validateForSend($campaign);
        if (!empty($errors)) {
            throw ValidationException::withMessages(['campaign' => $errors]);
        }

        $campaign->update([
            'scheduled_at' => $scheduledAt,
            'timezone' => $timezone,
        ]);

        $campaign->transitionTo(Campaign::STATUS_SCHEDULED);

        Log::info('[CampaignService] Campaign scheduled', [
            'campaign_id' => $campaign->id,
            'scheduled_at' => $scheduledAt,
            'timezone' => $timezone,
        ]);

        return $campaign;
    }

    /**
     * Process a scheduled campaign that's due for send.
     * Called by the ScheduledCampaignDispatcher job.
     *
     * Forces content re-resolution because contact data may have changed
     * between scheduling and the actual send time.
     */
    public function processScheduled(Campaign $campaign): PreflightResult
    {
        if (!$campaign->isScheduled()) {
            throw new \RuntimeException("Campaign is not in scheduled status.");
        }

        // Force re-resolution — contact data may have changed since scheduling
        $campaign->update(['content_resolved_at' => null]);

        // Billing preflight
        $preflightResult = $this->billingPreflight->runPreflight($campaign);

        // Resolve per-recipient content (re-runs because content_resolved_at was cleared)
        $this->resolveRecipientContent($campaign);

        // Transition to queued
        $campaign->transitionTo(Campaign::STATUS_QUEUED);

        Log::info('[CampaignService] Scheduled campaign queued for send', [
            'campaign_id' => $campaign->id,
        ]);

        return $preflightResult;
    }

    /**
     * Pause an in-progress campaign.
     */
    public function pause(Campaign $campaign): Campaign
    {
        $campaign->transitionTo(Campaign::STATUS_PAUSED);

        Log::info('[CampaignService] Campaign paused', [
            'campaign_id' => $campaign->id,
            'sent_so_far' => $campaign->sent_count,
        ]);

        return $campaign;
    }

    /**
     * Resume a paused campaign.
     */
    public function resume(Campaign $campaign): Campaign
    {
        $campaign->transitionTo(Campaign::STATUS_SENDING);

        Log::info('[CampaignService] Campaign resumed', [
            'campaign_id' => $campaign->id,
        ]);

        return $campaign;
    }

    /**
     * Cancel a campaign.
     * Releases any fund reservation.
     */
    public function cancel(Campaign $campaign): Campaign
    {
        $campaign->transitionTo(Campaign::STATUS_CANCELLED);

        // Release fund reservation if exists
        $this->billingPreflight->releaseReservation($campaign);

        Log::info('[CampaignService] Campaign cancelled', [
            'campaign_id' => $campaign->id,
        ]);

        return $campaign;
    }

    /**
     * Mark a campaign as completed.
     * Called by the queue system when all recipients have been processed.
     */
    public function complete(Campaign $campaign): Campaign
    {
        $campaign->transitionTo(Campaign::STATUS_COMPLETED);

        // Release any unused reservation funds
        $this->billingPreflight->releaseReservation($campaign);

        // Update final counts from campaign_recipients
        $this->refreshCampaignCounts($campaign);

        Log::info('[CampaignService] Campaign completed', [
            'campaign_id' => $campaign->id,
            'delivered' => $campaign->delivered_count,
            'failed' => $campaign->failed_count,
            'actual_cost' => $campaign->actual_cost,
        ]);

        return $campaign;
    }

    /**
     * Mark a campaign as failed.
     */
    public function fail(Campaign $campaign, string $reason): Campaign
    {
        $campaign->transitionTo(Campaign::STATUS_FAILED);

        $campaign->update([
            'metadata' => array_merge($campaign->metadata ?? [], [
                'failure_reason' => $reason,
                'failed_at' => now()->toIso8601String(),
            ]),
        ]);

        // Release fund reservation
        $this->billingPreflight->releaseReservation($campaign);

        Log::error('[CampaignService] Campaign failed', [
            'campaign_id' => $campaign->id,
            'reason' => $reason,
        ]);

        return $campaign;
    }

    // =====================================================
    // CLONE
    // =====================================================

    /**
     * Clone an existing campaign as a new draft.
     */
    public function clone(Campaign $original, ?string $newName = null): Campaign
    {
        $clone = $this->create($original->account_id, [
            'name' => $newName ?? $original->name . ' (Copy)',
            'description' => $original->description,
            'type' => $original->type,
            'message_template_id' => $original->message_template_id,
            'message_content' => $original->message_content,
            'rcs_content' => $original->rcs_content,
            'encoding' => $original->encoding,
            'segment_count' => $original->segment_count,
            'sender_id_id' => $original->sender_id_id,
            'rcs_agent_id' => $original->rcs_agent_id,
            'recipient_sources' => $original->recipient_sources,
            'send_rate' => $original->send_rate,
            'batch_size' => $original->batch_size,
            'tags' => $original->tags,
            'created_by' => $original->created_by,
        ]);

        Log::info('[CampaignService] Campaign cloned', [
            'original_id' => $original->id,
            'clone_id' => $clone->id,
        ]);

        return $clone;
    }

    // =====================================================
    // SOFT DELETE
    // =====================================================

    /**
     * Soft-delete a campaign.
     * Only draft or terminal campaigns can be deleted.
     */
    public function delete(Campaign $campaign): bool
    {
        if (!in_array($campaign->status, [
            Campaign::STATUS_DRAFT,
            Campaign::STATUS_COMPLETED,
            Campaign::STATUS_CANCELLED,
            Campaign::STATUS_FAILED,
        ])) {
            throw new \RuntimeException(
                "Cannot delete campaign in '{$campaign->status}' status. Only draft or terminal campaigns can be deleted."
            );
        }

        // Release reservation if exists
        if ($campaign->reservation_id) {
            $this->billingPreflight->releaseReservation($campaign);
        }

        $campaign->delete();

        Log::info('[CampaignService] Campaign deleted', [
            'campaign_id' => $campaign->id,
        ]);

        return true;
    }

    // =====================================================
    // INTERNAL HELPERS
    // =====================================================

    /**
     * Resolve merge fields in message content for all pending recipients.
     * Pre-computes resolved_content, encoding, and segments per-recipient
     * so the delivery worker doesn't need to, and cost estimation is accurate.
     *
     * Also resolves placeholders in RCS card text fields (description, textBody)
     * and stores the resolved rcs_content per-recipient in metadata.
     *
     * Skips if content has already been resolved (e.g. during prepareCampaign).
     */
    public function resolveRecipientContent(Campaign $campaign): void
    {
        // Skip if already resolved (e.g. done during campaign preparation)
        if ($campaign->content_resolved_at) {
            return;
        }

        // Resolve RCS content placeholders if applicable
        $hasRcsPlaceholders = false;
        if ($campaign->rcs_content && in_array($campaign->type, [Campaign::TYPE_RCS_SINGLE, Campaign::TYPE_RCS_CAROUSEL])) {
            $rcsText = $this->extractRcsTextContent($campaign->rcs_content);
            $hasRcsPlaceholders = !empty(MessageTemplate::extractPlaceholders($rcsText));
        }

        if (!$campaign->message_content && !$hasRcsPlaceholders) {
            $campaign->update(['content_resolved_at' => now()]);
            return;
        }

        // Check for SMS/message_content placeholders
        $hasSmsPlaceholders = $campaign->message_content
            ? !empty(MessageTemplate::extractPlaceholders($campaign->message_content))
            : false;

        if (!$hasSmsPlaceholders && !$hasRcsPlaceholders) {
            // No placeholders anywhere — bulk update with identical content
            if ($campaign->message_content) {
                $encoding = MessageTemplate::detectEncoding($campaign->message_content);

                DB::table('campaign_recipients')
                    ->where('campaign_id', $campaign->id)
                    ->where('status', CampaignRecipient::STATUS_PENDING)
                    ->whereNull('resolved_content')
                    ->update([
                        'resolved_content' => $campaign->message_content,
                        'segments' => $campaign->segment_count ?: 1,
                        'encoding' => $encoding,
                    ]);
            }

            $campaign->update(['content_resolved_at' => now()]);
            return;
        }

        // Process in chunks with batch UPDATE for performance.
        $batchUpdateSize = 500;

        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->whereNull('resolved_content')
            ->orderBy('id')
            ->chunk(2000, function ($recipients) use ($campaign, $batchUpdateSize, $hasRcsPlaceholders) {
                $pendingUpdates = [];

                foreach ($recipients as $row) {
                    $recipient = new CampaignRecipient();
                    $recipient->setRawAttributes((array) $row, true);
                    $recipient->exists = true;

                    $resolvedContent = $campaign->message_content
                        ? $recipient->resolveContent($campaign->message_content)
                        : null;

                    $encoding = $resolvedContent ? MessageTemplate::detectEncoding($resolvedContent) : null;
                    $segments = $resolvedContent ? MessageTemplate::calculateSegments($resolvedContent, $encoding) : ($campaign->segment_count ?: 1);

                    $update = [
                        'id' => $row->id,
                        'resolved_content' => $resolvedContent,
                        'segments' => $segments,
                        'encoding' => $encoding,
                    ];

                    // Resolve RCS card placeholders per-recipient
                    if ($hasRcsPlaceholders && $campaign->rcs_content) {
                        $resolvedRcs = $this->resolveRcsPlaceholders($campaign->rcs_content, $recipient);
                        $existingMeta = json_decode($row->metadata ?? '{}', true) ?: [];
                        $existingMeta['resolved_rcs_content'] = $resolvedRcs;
                        $update['metadata'] = json_encode($existingMeta);
                    }

                    $pendingUpdates[] = $update;

                    if (count($pendingUpdates) >= $batchUpdateSize) {
                        $this->flushBatchContentUpdate($pendingUpdates, $hasRcsPlaceholders);
                        $pendingUpdates = [];
                    }
                }

                if (!empty($pendingUpdates)) {
                    $this->flushBatchContentUpdate($pendingUpdates, $hasRcsPlaceholders);
                }
            });

        $campaign->update(['content_resolved_at' => now()]);
    }

    /**
     * Extract all text content from RCS cards for placeholder detection.
     */
    private function extractRcsTextContent(array $rcsContent): string
    {
        $texts = [];
        foreach ($rcsContent['cards'] ?? [] as $card) {
            if (!empty($card['description'])) {
                $texts[] = $card['description'];
            }
            if (!empty($card['textBody'])) {
                $texts[] = $card['textBody'];
            }
        }
        return implode(' ', $texts);
    }

    /**
     * Resolve placeholders in RCS content card text fields for a specific recipient.
     */
    private function resolveRcsPlaceholders(array $rcsContent, CampaignRecipient $recipient): array
    {
        $resolved = $rcsContent;

        foreach ($resolved['cards'] ?? [] as $cardIndex => $card) {
            if (!empty($card['description'])) {
                $resolved['cards'][$cardIndex]['description'] = $recipient->resolveContent($card['description']);
            }
            if (!empty($card['textBody'])) {
                $resolved['cards'][$cardIndex]['textBody'] = $recipient->resolveContent($card['textBody']);
            }
        }

        return $resolved;
    }

    /**
     * Flush a batch of resolved content updates using a single SQL statement.
     * Uses PostgreSQL UPDATE FROM VALUES for ~60x faster bulk updates.
     */
    private function flushBatchContentUpdate(array $updates, bool $includeMetadata = false): void
    {
        if (empty($updates)) {
            return;
        }

        if ($includeMetadata) {
            $values = [];
            $bindings = [];

            foreach ($updates as $update) {
                $values[] = '(?, ?, ?, ?, ?)';
                $bindings[] = $update['id'];
                $bindings[] = $update['resolved_content'];
                $bindings[] = $update['segments'];
                $bindings[] = $update['encoding'];
                $bindings[] = $update['metadata'] ?? '{}';
            }

            $valuesSql = implode(', ', $values);

            DB::statement("
                UPDATE campaign_recipients AS cr
                SET resolved_content = v.resolved_content,
                    segments = v.segments::integer,
                    encoding = v.encoding,
                    metadata = v.metadata::jsonb
                FROM (VALUES {$valuesSql}) AS v(id, resolved_content, segments, encoding, metadata)
                WHERE cr.id = v.id::uuid
            ", $bindings);
        } else {
            $values = [];
            $bindings = [];

            foreach ($updates as $update) {
                $values[] = '(?, ?, ?, ?)';
                $bindings[] = $update['id'];
                $bindings[] = $update['resolved_content'];
                $bindings[] = $update['segments'];
                $bindings[] = $update['encoding'];
            }

            $valuesSql = implode(', ', $values);

            DB::statement("
                UPDATE campaign_recipients AS cr
                SET resolved_content = v.resolved_content,
                    segments = v.segments::integer,
                    encoding = v.encoding
                FROM (VALUES {$valuesSql}) AS v(id, resolved_content, segments, encoding)
                WHERE cr.id = v.id::uuid
            ", $bindings);
        }
    }

    /**
     * Refresh campaign delivery counts from campaign_recipients aggregates.
     */
    private function refreshCampaignCounts(Campaign $campaign): void
    {
        $counts = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->select([
                DB::raw("COUNT(*) FILTER (WHERE status = 'sent') as sent_count"),
                DB::raw("COUNT(*) FILTER (WHERE status = 'delivered') as delivered_count"),
                DB::raw("COUNT(*) FILTER (WHERE status IN ('failed', 'undeliverable')) as failed_count"),
                DB::raw("COUNT(*) FILTER (WHERE status = 'pending' OR status = 'queued') as pending_count"),
                DB::raw("COALESCE(SUM(cost), 0) as actual_cost"),
            ])
            ->first();

        if ($counts) {
            $campaign->update([
                'sent_count' => $counts->sent_count,
                'delivered_count' => $counts->delivered_count,
                'failed_count' => $counts->failed_count,
                'pending_count' => $counts->pending_count,
                'actual_cost' => $counts->actual_cost,
            ]);
        }
    }
}

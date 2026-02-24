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
            'validity_period' => $data['validity_period'] ?? null,
            'sending_window_start' => $data['sending_window_start'] ?? null,
            'sending_window_end' => $data['sending_window_end'] ?? null,
            'send_rate' => $data['send_rate'] ?? 0,
            'batch_size' => $data['batch_size'] ?? Campaign::DEFAULT_BATCH_SIZE,
            'tags' => $data['tags'] ?? [],
            'metadata' => $data['metadata'] ?? [],
            'created_by' => $data['created_by'] ?? null,
        ]);

        // Auto-calculate encoding/segments if SMS content provided
        if ($campaign->message_content && in_array($campaign->type, [Campaign::TYPE_SMS, Campaign::TYPE_RCS_BASIC])) {
            $campaign->encoding = MessageTemplate::detectEncoding($campaign->message_content);
            $campaign->segment_count = MessageTemplate::calculateSegments($campaign->message_content, $campaign->encoding);
            $campaign->save();
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
     * @throws \RuntimeException if campaign is not in draft status
     */
    public function prepareCampaign(Campaign $campaign): ResolverResult
    {
        if (!$campaign->isDraft()) {
            throw new \RuntimeException("Campaign must be in draft status to prepare.");
        }

        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->delete();

        $campaign->update([
            'preparation_status' => 'preparing',
            'preparation_progress' => 0,
            'preparation_error' => null,
            'content_resolved_at' => null,
        ]);

        $result = $this->recipientResolver->resolve($campaign);

        $campaign->update([
            'total_recipients' => $result->totalResolved,
            'total_unique_recipients' => $result->totalResolved,
        ]);

        ResolveRecipientContentJob::dispatch($campaign->id);

        Log::info('[CampaignService] Campaign preparation started', [
            'campaign_id' => $campaign->id,
            'recipients_resolved' => $result->totalResolved,
        ]);

        return $result;
    }

    /**
     * Get preparation status for polling.
     */
    public function getPreparationStatus(Campaign $campaign): array
    {
        return [
            'status' => $campaign->preparation_status,
            'progress' => $campaign->preparation_progress,
            'error' => $campaign->preparation_error,
            'content_resolved' => $campaign->content_resolved_at !== null,
            'total_recipients' => $campaign->total_recipients,
        ];
    }

    // =====================================================
    // COST ESTIMATION
    // =====================================================

    /**
     * Get a cost estimate for the campaign.
     * Requires recipients to be resolved first.
     */
    public function estimateCost(Campaign $campaign): CostEstimate
    {
        $account = Account::findOrFail($campaign->account_id);

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
        } elseif ($campaign->type === Campaign::TYPE_RCS_SINGLE) {
            if (empty($campaign->rcs_content)) {
                $errors[] = 'RCS rich content is required.';
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
     */
    public function processScheduled(Campaign $campaign): PreflightResult
    {
        if (!$campaign->isScheduled()) {
            throw new \RuntimeException("Campaign is not in scheduled status.");
        }

        // Billing preflight
        $preflightResult = $this->billingPreflight->runPreflight($campaign);

        // Resolve per-recipient content
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
     * Resolve merge fields for all campaign recipients.
     *
     * Skips if content has already been resolved (e.g. during prepareCampaign).
     */
    public function resolveRecipientContent(Campaign $campaign): void
    {
        if ($campaign->content_resolved_at) {
            return;
        }

        if (!$campaign->message_content) {
            $campaign->update(['content_resolved_at' => now()]);
            return;
        }

        $placeholders = MessageTemplate::extractPlaceholders($campaign->message_content);
        if (empty($placeholders)) {
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

            $campaign->update(['content_resolved_at' => now()]);
            return;
        }

        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->whereNull('resolved_content')
            ->orderBy('id')
            ->chunk(2000, function ($recipients) use ($campaign) {
                foreach ($recipients as $row) {
                    $recipient = new CampaignRecipient((array) $row);
                    $recipient->exists = true;
                    $recipient->id = $row->id;

                    $resolvedContent = $recipient->resolveContent($campaign->message_content);
                    $encoding = MessageTemplate::detectEncoding($resolvedContent);
                    $segments = MessageTemplate::calculateSegments($resolvedContent, $encoding);

                    DB::table('campaign_recipients')
                        ->where('id', $row->id)
                        ->update([
                            'resolved_content' => $resolvedContent,
                            'segments' => $segments,
                            'encoding' => $encoding,
                        ]);
                }
            });

        $campaign->update(['content_resolved_at' => now()]);
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

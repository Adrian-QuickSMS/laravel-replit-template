<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\ImmutableAuditLog;

/**
 * Campaign Audit Log — immutable record of campaign lifecycle events.
 *
 * DATA CLASSIFICATION: Confidential - Audit Trail
 * TENANT ISOLATION: account_id + RLS
 *
 * Events: campaign_created, campaign_edited, campaign_prepared, campaign_sent,
 *         campaign_scheduled, campaign_paused, campaign_resumed, campaign_cancelled,
 *         campaign_completed, campaign_archived, campaign_cloned, campaign_deleted
 */
class CampaignAuditLog extends Model
{
    use ImmutableAuditLog;

    protected $table = 'campaign_audit_log';

    protected $fillable = [
        'account_id',
        'campaign_id',
        'action',
        'user_id',
        'user_name',
        'details',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'campaign_id' => 'string',
        'user_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'metadata' => '{}',
    ];

    protected static function boot()
    {
        parent::boot();
        static::applyTenantScope();
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeForCampaign(Builder $query, string $campaignId): Builder
    {
        return $query->where('campaign_id', $campaignId);
    }

    // =====================================================
    // FACTORY METHOD
    // =====================================================

    public static function record(
        string $accountId,
        string $campaignId,
        string $action,
        ?string $userId = null,
        ?string $userName = null,
        ?string $details = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::withoutGlobalScopes()->create([
            'account_id' => $accountId,
            'campaign_id' => $campaignId,
            'action' => $action,
            'user_id' => $userId,
            'user_name' => $userName,
            'details' => $details,
            'metadata' => \App\Services\Audit\AuditContext::sanitize($metadata),
            'ip_address' => $ipAddress ?? request()?->ip(),
            'user_agent' => $userAgent ?? request()?->userAgent(),
        ]);
    }
}

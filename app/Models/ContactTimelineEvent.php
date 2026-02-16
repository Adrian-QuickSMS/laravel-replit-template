<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * GREEN SIDE: Contact Timeline Event (audit trail — read-only from app)
 *
 * DATA CLASSIFICATION: Confidential - Audit Trail
 * SIDE: GREEN (customer portal accessible — masked PII)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 *
 * NOTE: This model is append-only. The application should never update or delete rows.
 * The underlying table is partitioned by month on created_at.
 * Primary key is composite: (event_id, created_at).
 */
class ContactTimelineEvent extends Model
{
    protected $table = 'contact_timeline_events';
    protected $primaryKey = 'event_id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'contact_id',
        'msisdn_hash',
        'event_type',
        'source_module',
        'actor_type',
        'actor_id',
        'actor_name',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'event_id' => 'string',
        'account_id' => 'string',
        'contact_id' => 'string',
        'actor_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'metadata' => '{}',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('contact_timeline_events.account_id', auth()->user()->tenant_id);
            }
        });
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeForContact($query, string $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    public function scopeOfType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeFromModule($query, string $sourceModule)
    {
        return $query->where('source_module', $sourceModule);
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }

    public function toPortalArray(): array
    {
        return [
            'event_id' => $this->event_id,
            'contact_id' => $this->contact_id,
            'msisdn_hash' => $this->msisdn_hash,
            'timestamp' => $this->created_at?->toIso8601String(),
            'event_type' => $this->event_type,
            'source_module' => $this->source_module,
            'actor_type' => $this->actor_type,
            'actor_id' => $this->actor_id,
            'actor_name' => $this->actor_name,
            'metadata' => $this->metadata ?? [],
        ];
    }
}

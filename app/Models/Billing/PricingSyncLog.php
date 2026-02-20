<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PricingSyncLog extends Model
{
    use HasUuids;

    protected $table = 'pricing_sync_log';
    public $timestamps = false;

    protected $fillable = [
        'account_id', 'field_path', 'old_value', 'new_value',
        'source', 'hubspot_timestamp', 'admin_timestamp',
        'conflict_detected', 'conflict_resolved',
        'resolved_by', 'resolved_at', 'resolution',
    ];

    protected $casts = [
        'conflict_detected' => 'boolean',
        'conflict_resolved' => 'boolean',
        'hubspot_timestamp' => 'datetime',
        'admin_timestamp' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeUnresolvedConflicts($query)
    {
        return $query->where('conflict_detected', true)->where('conflict_resolved', false);
    }
}

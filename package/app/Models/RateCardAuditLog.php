<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateCardAuditLog extends Model
{
    use HasFactory;

    protected $table = 'rate_card_audit_log';

    protected $fillable = [
        'rate_card_id',
        'supplier_id',
        'gateway_id',
        'action',
        'admin_user',
        'admin_email',
        'ip_address',
        'old_value',
        'new_value',
        'reason',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function rateCard()
    {
        return $this->belongsTo(RateCard::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    // Static method to log action
    public static function logAction($action, $data)
    {
        return self::create([
            'rate_card_id' => $data['rate_card_id'] ?? null,
            'supplier_id' => $data['supplier_id'],
            'gateway_id' => $data['gateway_id'] ?? null,
            'action' => $action,
            'admin_user' => $data['admin_user'] ?? auth()->user()->name ?? 'System',
            'admin_email' => $data['admin_email'] ?? auth()->user()->email ?? null,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'old_value' => $data['old_value'] ?? null,
            'new_value' => $data['new_value'] ?? null,
            'reason' => $data['reason'] ?? null,
        ]);
    }
}

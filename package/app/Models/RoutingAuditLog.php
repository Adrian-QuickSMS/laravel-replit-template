<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutingAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'routing_audit_log';

    protected $fillable = [
        'admin_user',
        'admin_ip',
        'action',
        'entity_type',
        'entity_id',
        'product_type',
        'destination',
        'old_value',
        'new_value',
        'reason',
        'created_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function routingRule()
    {
        return $this->belongsTo(RoutingRule::class, 'entity_id')
            ->where('entity_type', 'routing_rule');
    }

    public function gatewayWeight()
    {
        return $this->belongsTo(RoutingGatewayWeight::class, 'entity_id')
            ->where('entity_type', 'routing_gateway_weight');
    }

    public function customerOverride()
    {
        return $this->belongsTo(RoutingCustomerOverride::class, 'entity_id')
            ->where('entity_type', 'routing_customer_override');
    }

    // Scopes
    public function scopeForEntity($query, $entityType, $entityId)
    {
        return $query->where('entity_type', $entityType)
            ->where('entity_id', $entityId);
    }

    public function scopeByAdmin($query, $adminUser)
    {
        return $query->where('admin_user', $adminUser);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Static helper to log actions
    public static function logAction($action, $data)
    {
        return self::create([
            'admin_user' => $data['admin_user'] ?? auth()->user()->name ?? 'SYSTEM',
            'admin_ip' => $data['admin_ip'] ?? request()->ip() ?? '0.0.0.0',
            'action' => $action,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id' => $data['entity_id'] ?? null,
            'product_type' => $data['product_type'] ?? null,
            'destination' => $data['destination'] ?? null,
            'old_value' => $data['old_value'] ?? null,
            'new_value' => $data['new_value'] ?? null,
            'reason' => $data['reason'] ?? null,
            'created_at' => now(),
        ]);
    }
}

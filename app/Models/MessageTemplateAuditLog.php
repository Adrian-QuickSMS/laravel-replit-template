<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplateAuditLog extends Model
{
    protected $table = 'message_template_audit_log';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'account_id',
        'action',
        'version',
        'user_id',
        'user_name',
        'details',
        'created_at',
    ];

    protected $casts = [
        'id' => 'string',
        'template_id' => 'string',
        'account_id' => 'string',
        'version' => 'integer',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('message_template_audit_log.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function toPortalArray(): array
    {
        return [
            'action' => $this->action,
            'version' => $this->version,
            'userId' => $this->user_id ?? 'system',
            'userName' => $this->user_name ?? 'System',
            'timestamp' => $this->created_at?->format('Y-m-d H:i:s') ?? '',
            'details' => $this->details ?? '',
        ];
    }
}

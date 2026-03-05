<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplateVersion extends Model
{
    protected $table = 'message_template_versions';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'template_id',
        'account_id',
        'version',
        'snapshot',
        'change_note',
        'edited_by',
        'created_at',
    ];

    protected $casts = [
        'id' => 'string',
        'template_id' => 'string',
        'account_id' => 'string',
        'version' => 'integer',
        'snapshot' => 'array',
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
                $builder->where('message_template_versions.account_id', $tenantId);
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
        $snapshot = $this->snapshot ?? [];
        $status = $snapshot['status'] ?? 'draft';
        if ($status === 'active') {
            $status = 'live';
        }
        return [
            'version' => $this->version,
            'status' => $status,
            'content' => $snapshot['content'] ?? '',
            'channel' => $snapshot['type'] ?? 'sms',
            'trigger' => 'portal',
            'changeNote' => $this->change_note ?? '',
            'editedBy' => $this->edited_by ?? 'System',
            'editedAt' => $this->created_at?->format('Y-m-d H:i:s') ?? '',
            'userId' => $this->edited_by ?? 'system',
        ];
    }
}

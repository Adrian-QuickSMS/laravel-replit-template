<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Notification extends Model
{
    public $timestamps = false;

    protected $table = 'notifications';

    protected $fillable = [
        'uuid',
        'tenant_id',
        'user_id',
        'type',
        'severity',
        'title',
        'body',
        'deep_link',
        'meta',
        'read_at',
        'dismissed_at',
        'resolved_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at')->whereNull('resolved_at');
    }

    public function scopeUndismissed(Builder $query): Builder
    {
        return $query->whereNull('dismissed_at');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsDismissed(): void
    {
        $this->update([
            'dismissed_at' => now(),
            'read_at' => $this->read_at ?? now(),
        ]);
    }

    public function markAsResolved(): void
    {
        $this->update(['resolved_at' => now()]);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at) && is_null($this->resolved_at);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminNotification extends Model
{
    public $timestamps = false;

    protected $table = 'admin_notifications';

    protected $fillable = [
        'uuid',
        'recipient_admin_id',
        'type',
        'severity',
        'category',
        'title',
        'body',
        'deep_link',
        'action_url',
        'action_label',
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
        return $query->whereNull('read_at');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeOfSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    public function scopeUndismissed(Builder $query): Builder
    {
        return $query->whereNull('dismissed_at');
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
}

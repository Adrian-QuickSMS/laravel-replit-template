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
        'title',
        'body',
        'deep_link',
        'meta',
        'read_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'read_at' => 'datetime',
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

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}

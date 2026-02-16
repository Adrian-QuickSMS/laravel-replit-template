<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SenderIdComment extends Model
{
    public $timestamps = false;

    protected $table = 'sender_id_comments';

    const TYPE_INTERNAL = 'internal';
    const TYPE_CUSTOMER = 'customer';

    const ACTOR_ADMIN = 'admin';
    const ACTOR_CUSTOMER = 'customer';
    const ACTOR_SYSTEM = 'system';

    protected $fillable = [
        'uuid',
        'sender_id_id',
        'comment_type',
        'comment_text',
        'created_by_actor_type',
        'created_by_actor_id',
        'created_by_actor_name',
    ];

    protected $casts = [
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

    public function senderId(): BelongsTo
    {
        return $this->belongsTo(SenderId::class, 'sender_id_id');
    }

    public function scopeCustomerVisible(Builder $query): Builder
    {
        return $query->where('comment_type', self::TYPE_CUSTOMER);
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('comment_type', self::TYPE_INTERNAL);
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'comment_text' => $this->comment_text,
            'created_by_actor_type' => $this->created_by_actor_type === self::ACTOR_ADMIN
                ? 'QuickSMS Review Team'
                : 'Customer',
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'comment_type' => $this->comment_type,
            'comment_text' => $this->comment_text,
            'created_by_actor_type' => $this->created_by_actor_type,
            'created_by_actor_id' => $this->created_by_actor_id,
            'created_by_actor_name' => $this->created_by_actor_name,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

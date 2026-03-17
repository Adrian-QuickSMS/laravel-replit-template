<?php

namespace App\Models\Alerting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotificationBatch extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'notification_batches';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'batch_type',
        'channel',
        'items',
        'scheduled_for',
        'dispatched_at',
        'created_at',
    ];

    protected $casts = [
        'items' => 'array',
        'scheduled_for' => 'datetime',
        'dispatched_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });
    }

    // --- Scopes ---

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('dispatched_at');
    }

    public function scopeReady(Builder $query): Builder
    {
        return $query->whereNull('dispatched_at')
            ->where('scheduled_for', '<=', now());
    }

    public function scopeForBatchType(Builder $query, string $batchType): Builder
    {
        return $query->where('batch_type', $batchType);
    }

    // --- Business Logic ---

    public function addItem(array $item): void
    {
        $items = $this->items ?? [];
        $items[] = $item;
        $this->update(['items' => $items]);
    }

    public function markDispatched(): void
    {
        $this->update(['dispatched_at' => now()]);
    }

    public function itemCount(): int
    {
        return count($this->items ?? []);
    }
}

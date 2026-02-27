<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RcsAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'account_id',
        'user_id',
        'source_type',
        'source_url',
        'original_storage_path',
        'storage_path',
        'public_url',
        'mime_type',
        'file_size',
        'width',
        'height',
        'edit_params',
        'is_draft',
        'draft_session',
    ];

    protected $casts = [
        'edit_params' => 'array',
        'is_draft' => 'boolean',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('rcs_assets.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function scopeDrafts($query)
    {
        return $query->where('is_draft', true);
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_draft', false);
    }

    public function scopeStale($query, int $hours = 24)
    {
        return $query->where('is_draft', true)
            ->where('created_at', '<', now()->subHours($hours));
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}

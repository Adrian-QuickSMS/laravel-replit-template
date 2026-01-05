<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RcsAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'source_type',
        'source_url',
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
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}

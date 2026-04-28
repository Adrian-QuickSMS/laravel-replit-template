<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * GREEN SIDE: Platform Updates (Help Centre announcements).
 *
 * Global, non-tenant-scoped — every customer sees the same feed.
 * Per-user "read" status lives on the platform_update_reads pivot.
 */
class PlatformUpdate extends Model
{
    use HasUuids;

    public const TYPE_UPDATE = 'update';
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_FEATURE = 'feature';

    public const TYPES = [
        self::TYPE_UPDATE,
        self::TYPE_MAINTENANCE,
        self::TYPE_FEATURE,
    ];

    protected $table = 'platform_updates';

    protected $fillable = [
        'type',
        'title',
        'body',
        'posted_at',
        'link_url',
        'published',
    ];

    protected $casts = [
        'posted_at' => 'datetime',
        'published' => 'boolean',
    ];

    /**
     * Users who have marked this update as read.
     */
    public function reads(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'platform_update_reads',
            'platform_update_id',
            'user_id'
        )->withPivot('read_at');
    }
}

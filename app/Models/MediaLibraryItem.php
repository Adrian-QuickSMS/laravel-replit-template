<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * GREEN SIDE: Media Library Item (uploaded media for RCS campaigns)
 *
 * DATA CLASSIFICATION: Internal - Messaging Asset
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class MediaLibraryItem extends Model
{
    use SoftDeletes;

    protected $table = 'media_library';

    protected $keyType = 'string';
    public $incrementing = false;

    // =====================================================
    // MEDIA TYPE CONSTANTS
    // =====================================================

    const MEDIA_IMAGE = 'image';
    const MEDIA_VIDEO = 'video';
    const MEDIA_DOCUMENT = 'document';

    const MEDIA_TYPES = [
        self::MEDIA_IMAGE,
        self::MEDIA_VIDEO,
        self::MEDIA_DOCUMENT,
    ];

    // =====================================================
    // ALLOWED MIME TYPES
    // =====================================================

    const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'video/mp4',
        'application/pdf',
    ];

    // Map MIME types to media_type
    const MIME_TO_MEDIA_TYPE = [
        'image/jpeg' => self::MEDIA_IMAGE,
        'image/png' => self::MEDIA_IMAGE,
        'image/gif' => self::MEDIA_IMAGE,
        'image/webp' => self::MEDIA_IMAGE,
        'video/mp4' => self::MEDIA_VIDEO,
        'application/pdf' => self::MEDIA_DOCUMENT,
    ];

    // Max file sizes in bytes
    const MAX_IMAGE_SIZE = 5 * 1024 * 1024;    // 5 MB
    const MAX_VIDEO_SIZE = 100 * 1024 * 1024;   // 100 MB
    const MAX_DOCUMENT_SIZE = 10 * 1024 * 1024;  // 10 MB

    // =====================================================
    // MODEL CONFIGURATION
    // =====================================================

    protected $fillable = [
        'account_id',
        'filename',
        'storage_path',
        'storage_disk',
        'mime_type',
        'file_size',
        'media_type',
        'width',
        'height',
        'duration',
        'thumbnail_path',
        'alt_text',
        'title',
        'usage_count',
        'last_used_at',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'metadata' => '{}',
    ];

    // =====================================================
    // BOOT / TENANT SCOPE
    // =====================================================

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('media_library.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeImages($query)
    {
        return $query->where('media_type', self::MEDIA_IMAGE);
    }

    public function scopeVideos($query)
    {
        return $query->where('media_type', self::MEDIA_VIDEO);
    }

    public function scopeDocuments($query)
    {
        return $query->where('media_type', self::MEDIA_DOCUMENT);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('media_type', $type);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('filename', 'ilike', "%{$search}%")
              ->orWhere('title', 'ilike', "%{$search}%")
              ->orWhere('alt_text', 'ilike', "%{$search}%");
        });
    }

    // =====================================================
    // VALIDATION HELPERS
    // =====================================================

    /**
     * Check if a MIME type is allowed for upload.
     */
    public static function isAllowedMimeType(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_MIME_TYPES);
    }

    /**
     * Resolve media_type from MIME type.
     */
    public static function resolveMediaType(string $mimeType): ?string
    {
        return self::MIME_TO_MEDIA_TYPE[$mimeType] ?? null;
    }

    /**
     * Get the max allowed file size for a given media type.
     */
    public static function getMaxFileSize(string $mediaType): int
    {
        return match ($mediaType) {
            self::MEDIA_IMAGE => self::MAX_IMAGE_SIZE,
            self::MEDIA_VIDEO => self::MAX_VIDEO_SIZE,
            self::MEDIA_DOCUMENT => self::MAX_DOCUMENT_SIZE,
            default => self::MAX_IMAGE_SIZE,
        };
    }

    // =====================================================
    // FILE HELPERS
    // =====================================================

    /**
     * Get the full URL to the stored file.
     */
    public function getUrl(): ?string
    {
        if (!$this->storage_path) {
            return null;
        }
        return Storage::disk($this->storage_disk)->url($this->storage_path);
    }

    /**
     * Get the full URL to the thumbnail.
     */
    public function getThumbnailUrl(): ?string
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        return Storage::disk($this->storage_disk)->url($this->thumbnail_path);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Record a usage of this media item.
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Check if the file still exists in storage.
     */
    public function fileExists(): bool
    {
        return Storage::disk($this->storage_disk)->exists($this->storage_path);
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'filename' => $this->filename,
            'title' => $this->title,
            'alt_text' => $this->alt_text,
            'mime_type' => $this->mime_type,
            'media_type' => $this->media_type,
            'file_size' => $this->file_size,
            'file_size_formatted' => $this->getFormattedFileSize(),
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'url' => $this->getUrl(),
            'thumbnail_url' => $this->getThumbnailUrl(),
            'usage_count' => $this->usage_count,
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}

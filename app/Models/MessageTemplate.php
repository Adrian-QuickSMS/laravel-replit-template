<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * GREEN SIDE: Message Template (reusable SMS/RCS templates)
 *
 * DATA CLASSIFICATION: Internal - Messaging Asset
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 */
class MessageTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'message_templates';

    protected $keyType = 'string';
    public $incrementing = false;

    // =====================================================
    // TYPE CONSTANTS
    // =====================================================

    const TYPE_SMS = 'sms';
    const TYPE_RCS_BASIC = 'rcs_basic';
    const TYPE_RCS_SINGLE = 'rcs_single';
    const TYPE_RCS_CAROUSEL = 'rcs_carousel';

    const TYPES = [
        self::TYPE_SMS,
        self::TYPE_RCS_BASIC,
        self::TYPE_RCS_SINGLE,
        self::TYPE_RCS_CAROUSEL,
    ];

    // =====================================================
    // STATUS CONSTANTS
    // =====================================================

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_ARCHIVED = 'archived';

    const SUSPENDED_BY_ADMIN = 'admin';
    const SUSPENDED_BY_CUSTOMER = 'customer';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_SUSPENDED,
        self::STATUS_ARCHIVED,
    ];

    // =====================================================
    // SMS ENCODING CONSTANTS
    // =====================================================

    const ENCODING_GSM7 = 'gsm7';
    const ENCODING_UNICODE = 'unicode';

    // GSM-7 character limits per segment
    const GSM7_SINGLE_SEGMENT_LIMIT = 160;
    const GSM7_MULTI_SEGMENT_LIMIT = 153;

    // Unicode (UCS-2) character limits per segment
    const UNICODE_SINGLE_SEGMENT_LIMIT = 70;
    const UNICODE_MULTI_SEGMENT_LIMIT = 67;

    // =====================================================
    // MODEL CONFIGURATION
    // =====================================================

    protected $fillable = [
        'id',
        'account_id',
        'sub_account_id',
        'name',
        'description',
        'type',
        'trigger_type',
        'content',
        'rcs_content',
        'placeholders',
        'encoding',
        'character_count',
        'segment_count',
        'sender_id_id',
        'rcs_agent_id',
        'opt_out_enabled',
        'opt_out_method',
        'opt_out_number_id',
        'opt_out_keyword',
        'opt_out_text',
        'opt_out_list_id',
        'opt_out_url_enabled',
        'opt_out_screening_list_ids',
        'trackable_link_enabled',
        'trackable_link_domain',
        'message_expiry_enabled',
        'message_expiry_value',
        'social_hours_enabled',
        'social_hours_from',
        'social_hours_to',
        'status',
        'suspended_by',
        'version',
        'is_favourite',
        'category',
        'tags',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'sub_account_id' => 'string',
        'sender_id_id' => 'string',
        'rcs_agent_id' => 'string',
        'rcs_content' => 'array',
        'placeholders' => 'array',
        'tags' => 'array',
        'opt_out_screening_list_ids' => 'array',
        'character_count' => 'integer',
        'segment_count' => 'integer',
        'version' => 'integer',
        'is_favourite' => 'boolean',
        'opt_out_enabled' => 'boolean',
        'opt_out_url_enabled' => 'boolean',
        'opt_out_number_id' => 'string',
        'opt_out_list_id' => 'string',
        'trackable_link_enabled' => 'boolean',
        'message_expiry_enabled' => 'boolean',
        'social_hours_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'placeholders' => '[]',
        'tags' => '[]',
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
                $builder->where('message_templates.account_id', $tenantId);
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

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'message_template_id');
    }

    public function senderId(): BelongsTo
    {
        return $this->belongsTo(SenderId::class, 'sender_id_id', 'uuid');
    }

    public function rcsAgent(): BelongsTo
    {
        return $this->belongsTo(RcsAgent::class, 'rcs_agent_id', 'uuid');
    }

    public function optOutNumber(): BelongsTo
    {
        return $this->belongsTo(PurchasedNumber::class, 'opt_out_number_id');
    }

    public function optOutList(): BelongsTo
    {
        return $this->belongsTo(OptOutList::class, 'opt_out_list_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(MessageTemplateVersion::class, 'template_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(MessageTemplateAuditLog::class, 'template_id');
    }


    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSms($query)
    {
        return $query->where('type', self::TYPE_SMS);
    }

    public function scopeRcs($query)
    {
        return $query->whereIn('type', [self::TYPE_RCS_BASIC, self::TYPE_RCS_SINGLE]);
    }

    public function scopeFavourites($query)
    {
        return $query->where('is_favourite', true);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ilike', "%{$search}%")
              ->orWhere('description', 'ilike', "%{$search}%")
              ->orWhere('content', 'ilike', "%{$search}%");
        });
    }

    // =====================================================
    // SMS HELPERS
    // =====================================================

    /**
     * Detect encoding for a given text string.
     */
    public static function detectEncoding(string $text): string
    {
        // GSM-7 basic character set (including extension table chars)
        $gsm7Chars = "@\x{00A3}\$\x{00A5}\x{00E8}\x{00E9}\x{00F9}\x{00EC}\x{00F2}\x{00C7}\n\x{00D8}\x{00F8}\r\x{00C5}\x{00E5}\x{0394}\x{03A6}\x{0393}\x{039B}\x{03A9}\x{03A0}\x{03A8}\x{03A3}\x{0398}\x{039E} !\"#\x{00A4}%&'()*+,-./0123456789:;<=>?\x{00A1}ABCDEFGHIJKLMNOPQRSTUVWXYZ\x{00C4}\x{00D6}\x{00D1}\x{00DC}\x{00A7}\x{00BF}abcdefghijklmnopqrstuvwxyz\x{00E4}\x{00F6}\x{00F1}\x{00FC}\x{00E0}^{}\\[~]|\x{20AC}";

        // Check each character
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($chars as $char) {
            if (mb_strpos($gsm7Chars, $char) === false) {
                return self::ENCODING_UNICODE;
            }
        }

        return self::ENCODING_GSM7;
    }

    /**
     * Calculate segment count for a given text and encoding.
     */
    public static function calculateSegments(string $text, ?string $encoding = null): int
    {
        if (empty($text)) {
            return 0;
        }

        $encoding = $encoding ?? self::detectEncoding($text);
        $charCount = mb_strlen($text);

        if ($encoding === self::ENCODING_GSM7) {
            if ($charCount <= self::GSM7_SINGLE_SEGMENT_LIMIT) {
                return 1;
            }
            return (int) ceil($charCount / self::GSM7_MULTI_SEGMENT_LIMIT);
        }

        // Unicode
        if ($charCount <= self::UNICODE_SINGLE_SEGMENT_LIMIT) {
            return 1;
        }
        return (int) ceil($charCount / self::UNICODE_MULTI_SEGMENT_LIMIT);
    }

    /**
     * Detect merge field placeholders in content.
     * Returns array of field names like ['first_name', 'last_name', 'custom_data.loyalty_id']
     */
    public static function extractPlaceholders(string $content): array
    {
        preg_match_all('/\{\{(\s*[\w.]+\s*)\}\}/', $content, $matches);
        return array_map('trim', array_unique($matches[1] ?? []));
    }

    /**
     * Recalculate encoding, character count, segments, and placeholders from content.
     */
    public function recalculateMetadata(): self
    {
        if ($this->type === self::TYPE_SMS || $this->type === self::TYPE_RCS_BASIC) {
            $content = $this->content ?? '';
            $this->encoding = self::detectEncoding($content);
            $this->character_count = mb_strlen($content);
            $this->segment_count = self::calculateSegments($content, $this->encoding);
            $this->placeholders = self::extractPlaceholders($content);
        }

        return $this;
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'content' => $this->content,
            'rcs_content' => $this->rcs_content,
            'placeholders' => $this->placeholders,
            'encoding' => $this->encoding,
            'character_count' => $this->character_count,
            'segment_count' => $this->segment_count,
            'sender_id_id' => $this->sender_id_id,
            'rcs_agent_id' => $this->rcs_agent_id,
            'opt_out_enabled' => $this->opt_out_enabled,
            'opt_out_method' => $this->opt_out_method,
            'opt_out_number_id' => $this->opt_out_number_id,
            'opt_out_number' => $this->optOutNumber?->number,
            'opt_out_keyword' => $this->opt_out_keyword,
            'opt_out_text' => $this->opt_out_text,
            'opt_out_list_id' => $this->opt_out_list_id,
            'opt_out_url_enabled' => $this->opt_out_url_enabled,
            'opt_out_screening_list_ids' => $this->opt_out_screening_list_ids,
            'trackable_link_enabled' => $this->trackable_link_enabled,
            'trackable_link_domain' => $this->trackable_link_domain,
            'message_expiry_enabled' => $this->message_expiry_enabled,
            'message_expiry_value' => $this->message_expiry_value,
            'social_hours_enabled' => $this->social_hours_enabled,
            'social_hours_from' => $this->social_hours_from,
            'social_hours_to' => $this->social_hours_to,
            'status' => $this->status,
            'suspended_by' => $this->suspended_by,
            'version' => $this->version,
            'is_favourite' => $this->is_favourite,
            'category' => $this->category,
            'tags' => $this->tags,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

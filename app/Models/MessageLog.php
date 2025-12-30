<?php

namespace App\Models;

use App\Contracts\MessageLogRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * MessageLog Model
 * 
 * Represents a single message log entry in the system.
 * Implements MessageLogRecord interface for type safety.
 *
 * @property string $id
 * @property string $mobile_number
 * @property string $sender_id
 * @property string $status
 * @property \DateTime|null $sent_time
 * @property \DateTime|null $delivery_time
 * @property \DateTime|null $completed_time
 * @property float $cost
 * @property string $type
 * @property string $sub_account
 * @property string $user
 * @property string $origin
 * @property string $country
 * @property int $fragments
 * @property string $encoding
 * @property string $content_encrypted
 * @property bool $billable_flag
 */
class MessageLog extends Model implements MessageLogRecord
{
    /**
     * The table associated with the model.
     */
    protected $table = 'message_logs';

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'id',
        'mobile_number',
        'sender_id',
        'status',
        'sent_time',
        'delivery_time',
        'completed_time',
        'cost',
        'type',
        'sub_account',
        'user',
        'origin',
        'country',
        'fragments',
        'encoding',
        'content_encrypted',
        'billable_flag',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'sent_time' => 'datetime',
        'delivery_time' => 'datetime',
        'completed_time' => 'datetime',
        'cost' => 'float',
        'fragments' => 'integer',
        'billable_flag' => 'boolean',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'content_encrypted',
    ];

    /**
     * Valid status values.
     */
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_PENDING = 'pending';
    public const STATUS_UNDELIVERABLE = 'undeliverable';
    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_DELIVERED,
        self::STATUS_PENDING,
        self::STATUS_UNDELIVERABLE,
        self::STATUS_REJECTED,
    ];

    /**
     * Valid message type values.
     */
    public const TYPE_SMS = 'sms';
    public const TYPE_RCS_BASIC = 'rcs_basic';
    public const TYPE_RCS_RICH = 'rcs_rich';

    public const TYPES = [
        self::TYPE_SMS,
        self::TYPE_RCS_BASIC,
        self::TYPE_RCS_RICH,
    ];

    /**
     * Valid origin values.
     */
    public const ORIGIN_PORTAL = 'portal';
    public const ORIGIN_API = 'api';
    public const ORIGIN_EMAIL_TO_SMS = 'email_to_sms';
    public const ORIGIN_INTEGRATION = 'integration';

    public const ORIGINS = [
        self::ORIGIN_PORTAL,
        self::ORIGIN_API,
        self::ORIGIN_EMAIL_TO_SMS,
        self::ORIGIN_INTEGRATION,
    ];

    /**
     * Valid encoding values.
     */
    public const ENCODING_GSM7 = 'gsm7';
    public const ENCODING_UNICODE = 'unicode';

    public const ENCODINGS = [
        self::ENCODING_GSM7,
        self::ENCODING_UNICODE,
    ];

    // ========================================
    // MessageLogRecord Interface Implementation
    // ========================================

    public function getId(): string
    {
        return $this->id;
    }

    public function getMobileNumber(): string
    {
        return $this->mobile_number;
    }

    public function getMaskedMobileNumber(): string
    {
        $number = $this->mobile_number;
        if (strlen($number) < 10) {
            return $number;
        }
        
        $prefix = substr($number, 0, 5);
        $suffix = substr($number, -3);
        return $prefix . '** ***' . $suffix;
    }

    public function getSenderId(): string
    {
        return $this->sender_id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSentTime(): ?\DateTimeInterface
    {
        return $this->sent_time;
    }

    public function getDeliveryTime(): ?\DateTimeInterface
    {
        return $this->delivery_time;
    }

    public function getCompletedTime(): ?\DateTimeInterface
    {
        return $this->completed_time;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubAccount(): string
    {
        return $this->sub_account;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getFragments(): int
    {
        return $this->fragments;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function getContent(): string
    {
        if ($this->canViewContent()) {
            return $this->decryptContent();
        }
        return '••••••••';
    }

    public function isBillable(): bool
    {
        return $this->billable_flag;
    }

    // ========================================
    // Content Security Methods
    // ========================================

    /**
     * Check if the current user can view message content.
     * Only Super Admin role can view plaintext content.
     */
    protected function canViewContent(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        // TODO: Replace with actual role check
        // return $user->role === 'super_admin';
        // return $user->hasPermission('view_message_content');
        
        return false;
    }

    /**
     * Decrypt and return the message content.
     */
    protected function decryptContent(): string
    {
        if (empty($this->content_encrypted)) {
            return '';
        }

        try {
            return Crypt::decryptString($this->content_encrypted);
        } catch (\Exception $e) {
            return '[Decryption Error]';
        }
    }

    /**
     * Encrypt and store message content.
     */
    public function setContentAttribute(string $value): void
    {
        $this->attributes['content_encrypted'] = Crypt::encryptString($value);
    }

    // ========================================
    // Query Scopes
    // ========================================

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by message type.
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('sent_time', '>=', $from);
        }
        if ($to) {
            $query->where('sent_time', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope to filter by sub-account.
     */
    public function scopeSubAccount($query, string $subAccount)
    {
        return $query->where('sub_account', $subAccount);
    }

    /**
     * Scope to filter by origin.
     */
    public function scopeOrigin($query, string $origin)
    {
        return $query->where('origin', $origin);
    }

    /**
     * Scope to get only billable messages.
     */
    public function scopeBillable($query)
    {
        return $query->where('billable_flag', true);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Get the status badge CSS class.
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DELIVERED => 'bg-success',
            self::STATUS_PENDING => 'bg-warning text-dark',
            self::STATUS_UNDELIVERABLE => 'bg-danger',
            self::STATUS_REJECTED => 'bg-secondary',
            default => 'bg-light text-dark',
        };
    }

    /**
     * Get the message type badge CSS class.
     */
    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            self::TYPE_SMS => 'bg-secondary',
            self::TYPE_RCS_BASIC => 'bg-info',
            self::TYPE_RCS_RICH => 'bg-info',
            default => 'bg-light text-dark',
        };
    }

    /**
     * Get human-readable type label.
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_SMS => 'SMS',
            self::TYPE_RCS_BASIC => 'RCS Basic',
            self::TYPE_RCS_RICH => 'RCS Rich',
            default => $this->type,
        };
    }

    /**
     * Get human-readable encoding label.
     */
    public function getEncodingLabel(): string
    {
        return match ($this->encoding) {
            self::ENCODING_GSM7 => 'GSM-7',
            self::ENCODING_UNICODE => 'Unicode',
            default => $this->encoding,
        };
    }

    /**
     * Format cost for display.
     */
    public function getFormattedCost(): string
    {
        return '£' . number_format($this->cost, 3);
    }

    /**
     * Convert to array for API response.
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'mobile_number' => $this->getMaskedMobileNumber(),
            'mobile_number_raw' => $this->canViewContent() ? $this->getMobileNumber() : null,
            'sender_id' => $this->getSenderId(),
            'status' => $this->getStatus(),
            'status_badge_class' => $this->getStatusBadgeClass(),
            'sent_time' => $this->getSentTime()?->format('d/m/Y H:i'),
            'delivery_time' => $this->getDeliveryTime()?->format('d/m/Y H:i'),
            'completed_time' => $this->getCompletedTime()?->format('d/m/Y H:i'),
            'cost' => $this->getFormattedCost(),
            'type' => $this->getType(),
            'type_label' => $this->getTypeLabel(),
            'type_badge_class' => $this->getTypeBadgeClass(),
            'sub_account' => $this->getSubAccount(),
            'user' => $this->getUser(),
            'origin' => $this->getOrigin(),
            'country' => $this->getCountry(),
            'fragments' => $this->getFragments(),
            'encoding' => $this->getEncoding(),
            'encoding_label' => $this->getEncodingLabel(),
            'content' => $this->getContent(),
            'billable' => $this->isBillable(),
        ];
    }
}

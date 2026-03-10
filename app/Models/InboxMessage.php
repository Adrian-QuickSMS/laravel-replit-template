<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class InboxMessage extends Model
{
    protected $table = 'inbox_messages';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'account_id',
        'conversation_id',
        'direction',
        'channel',
        'from_number',
        'to_number',
        'content',
        'content_encrypted',
        'rcs_payload',
        'status',
        'message_log_id',
        'gateway_message_id',
        'cost',
        'fragments',
        'encoding',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'conversation_id' => 'string',
        'rcs_payload' => 'array',
        'cost' => 'decimal:4',
        'fragments' => 'integer',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');

            if ($tenantId) {
                $builder->where('inbox_messages.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });

        // Encrypt content on save
        static::saving(function (self $msg) {
            if ($msg->isDirty('content') && $msg->content !== null) {
                $msg->content_encrypted = Crypt::encryptString($msg->content);
            }
        });
    }

    /* ── Relationships ───────────────────────────────────── */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(InboxConversation::class, 'conversation_id');
    }

    /* ── Accessors ───────────────────────────────────────── */

    /**
     * Decrypt content for reading. Falls back to plaintext column.
     */
    public function getDecryptedContentAttribute(): ?string
    {
        if ($this->content_encrypted) {
            try {
                return Crypt::decryptString($this->content_encrypted);
            } catch (\Exception $e) {
                return $this->content;
            }
        }
        return $this->content;
    }

    /* ── Serialisation ───────────────────────────────────── */

    /**
     * Match the frontend message object contract.
     */
    public function toPortalArray(): array
    {
        $text = $this->decrypted_content ?? '';

        $data = [
            'id' => $this->id,
            'direction' => $this->direction,
            'content' => $text,
            'time' => $this->sent_at ? $this->sent_at->format('g:i A') : '',
            'date' => $this->sent_at ? $this->sent_at->format('d M Y') : '',
            'status' => $this->status,
            'channel' => $this->channel,
        ];

        if ($this->rcs_payload) {
            $data['type'] = 'rich_card';
            $data['rich_card'] = $this->rcs_payload;
            if ($text) {
                $data['caption'] = $text;
            }
        }

        return $data;
    }
}

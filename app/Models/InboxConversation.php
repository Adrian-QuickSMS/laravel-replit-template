<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InboxConversation extends Model
{
    protected $table = 'inbox_conversations';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'phone_number',
        'channel',
        'source',
        'source_type',
        'purchased_number_id',
        'rcs_agent_id',
        'contact_id',
        'sender_id',
        'status',
        'unread_count',
        'last_message_content',
        'last_message_direction',
        'last_message_at',
        'first_message_at',
        'awaiting_reply_since',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'purchased_number_id' => 'string',
        'rcs_agent_id' => 'string',
        'contact_id' => 'string',
        'unread_count' => 'integer',
        'last_message_at' => 'datetime',
        'first_message_at' => 'datetime',
        'awaiting_reply_since' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');

            if ($tenantId) {
                $builder->where('inbox_conversations.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
            }
        });
    }

    /* ── Relationships ───────────────────────────────────── */

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InboxMessage::class, 'conversation_id')->orderBy('sent_at');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function purchasedNumber(): BelongsTo
    {
        return $this->belongsTo(PurchasedNumber::class, 'purchased_number_id');
    }

    public function readReceipts(): HasMany
    {
        return $this->hasMany(InboxReadReceipt::class, 'conversation_id');
    }

    /* ── Scopes ──────────────────────────────────────────── */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('unread_count', '>', 0);
    }

    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    public function scopeAwaitingReply(Builder $query): Builder
    {
        return $query->whereNotNull('awaiting_reply_since');
    }

    public function scopeAwaitingReply48h(Builder $query): Builder
    {
        return $query->where('awaiting_reply_since', '<=', now()->subHours(48));
    }

    /* ── Methods ─────────────────────────────────────────── */

    public function markRead(User $user): void
    {
        InboxReadReceipt::updateOrCreate(
            ['conversation_id' => $this->id, 'user_id' => $user->id],
            ['account_id' => $this->account_id, 'last_read_at' => now()]
        );

        $this->update(['unread_count' => 0]);
    }

    public function markUnread(): void
    {
        $this->update(['unread_count' => max(1, $this->unread_count)]);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function block(): void
    {
        $this->update(['status' => 'blocked']);
    }

    public function isAwaitingReply48h(): bool
    {
        return $this->awaiting_reply_since
            && $this->awaiting_reply_since->lte(now()->subHours(48));
    }

    /**
     * Update denormalised conversation metadata after a new message.
     */
    public function touchFromMessage(InboxMessage $message): void
    {
        $updates = [
            'last_message_content' => mb_substr($message->content ?? '[Rich Message]', 0, 200),
            'last_message_direction' => $message->direction,
            'last_message_at' => $message->sent_at,
        ];

        if (!$this->first_message_at) {
            $updates['first_message_at'] = $message->sent_at;
        }

        if ($message->direction === 'inbound') {
            $updates['awaiting_reply_since'] = $message->sent_at;
        } else {
            $updates['awaiting_reply_since'] = null;
        }

        $this->update($updates);

        // Atomic increment for unread_count to prevent race conditions.
        // NOTE: After increment(), the in-memory $this->unread_count is stale.
        // Call $this->refresh() if you need the updated value downstream.
        if ($message->direction === 'inbound') {
            $this->increment('unread_count');
        }
    }

    /**
     * Serialise for the frontend JSON contract.
     */
    public function toPortalArray(): array
    {
        $phone = $this->phone_number;
        $masked = preg_replace('/(\+\d{2})\d{4}(\d{3})/', '$1 **** ***$2', $phone);

        $contact = $this->relationLoaded('contact') ? $this->contact : null;
        $name = $contact ? trim($contact->first_name . ' ' . $contact->last_name) : $masked;
        $initials = $contact
            ? strtoupper(mb_substr($contact->first_name, 0, 1) . mb_substr($contact->last_name, 0, 1))
            : '??';

        $data = [
            'id' => $this->id,
            'phone' => $phone,
            'phone_masked' => $masked,
            'name' => $name,
            'initials' => $initials,
            'contact_id' => $this->contact_id,
            'channel' => $this->channel,
            'source' => $this->source,
            'source_type' => $this->source_type,
            'sender_id' => $this->sender_id,
            'unread' => $this->unread_count > 0,
            'unread_count' => $this->unread_count,
            'last_message' => $this->last_message_content ?? '',
            'last_message_time' => $this->last_message_at
                ? $this->last_message_at->format('g:i A')
                : '',
            'timestamp' => $this->last_message_at
                ? $this->last_message_at->timestamp
                : 0,
            'first_contact' => $this->first_message_at
                ? $this->first_message_at->format('d M Y')
                : '',
            'awaiting_reply_48h' => $this->isAwaitingReply48h(),
        ];

        if ($this->rcs_agent_id) {
            $data['rcs_agent_id'] = $this->rcs_agent_id;
        }

        return $data;
    }
}

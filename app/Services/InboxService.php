<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\InboxConversation;
use App\Models\InboxMessage;
use App\Models\InboxReadReceipt;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InboxService
{
    /**
     * List conversations for the current tenant, with filters and pagination.
     */
    public function getConversations(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = InboxConversation::with('contact')
            ->active()
            ->orderByDesc('last_message_at');

        if (!empty($filters['channel'])) {
            $query->forChannel($filters['channel']);
        }

        if (!empty($filters['unread_only'])) {
            $query->unread();
        }

        if (!empty($filters['awaiting_reply'])) {
            $query->awaitingReply();
        }

        if (!empty($filters['search'])) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('phone_number', 'ILIKE', "%{$search}%")
                  ->orWhere('last_message_content', 'ILIKE', "%{$search}%")
                  ->orWhereHas('contact', function ($cq) use ($search) {
                      $cq->where('first_name', 'ILIKE', "%{$search}%")
                         ->orWhere('last_name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['since'])) {
            $query->where('updated_at', '>', $filters['since']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get all conversations as a flat array (for initial page load).
     */
    public function getConversationsArray(array $filters = []): array
    {
        $paginated = $this->getConversations($filters, 100);

        return collect($paginated->items())
            ->map(fn (InboxConversation $c) => $c->toPortalArray())
            ->values()
            ->all();
    }

    /**
     * Find a single conversation by ID with messages.
     */
    public function findConversation(string $id): ?InboxConversation
    {
        return InboxConversation::with(['contact', 'messages'])->find($id);
    }

    /**
     * Get paginated messages for a conversation.
     */
    public function getMessages(string $conversationId, int $perPage = 50, ?string $before = null): LengthAwarePaginator
    {
        $query = InboxMessage::where('conversation_id', $conversationId)
            ->orderByDesc('sent_at');

        if ($before) {
            $query->where('sent_at', '<', $before);
        }

        return $query->paginate($perPage);
    }

    /**
     * Total unread count across all conversations for the current tenant.
     */
    public function getUnreadCount(): int
    {
        return InboxConversation::active()->sum('unread_count');
    }

    /**
     * Find or create a conversation for a given phone + channel + source.
     */
    public function findOrCreateConversation(
        string $accountId,
        string $phoneNumber,
        string $channel,
        string $source,
        string $sourceType,
        ?string $purchasedNumberId = null,
        ?string $rcsAgentId = null,
        ?string $senderId = null
    ): InboxConversation {
        $conversation = InboxConversation::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('phone_number', $phoneNumber)
            ->where('channel', $channel)
            ->where('source', $source)
            ->first();

        if ($conversation) {
            // Reactivate if archived
            if ($conversation->status === 'archived') {
                $conversation->update(['status' => 'active']);
            }
            return $conversation;
        }

        // Try to link to a contact
        $contact = Contact::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('msisdn', $phoneNumber)
            ->first();

        return InboxConversation::withoutGlobalScope('tenant')->create([
            'account_id' => $accountId,
            'phone_number' => $phoneNumber,
            'channel' => $channel,
            'source' => $source,
            'source_type' => $sourceType,
            'purchased_number_id' => $purchasedNumberId,
            'rcs_agent_id' => $rcsAgentId,
            'contact_id' => $contact?->id,
            'sender_id' => $senderId,
        ]);
    }

    /**
     * Add a message to a conversation and update metadata.
     */
    public function addMessage(
        InboxConversation $conversation,
        string $direction,
        string $content,
        string $fromNumber,
        string $toNumber,
        array $extra = []
    ): InboxMessage {
        $message = InboxMessage::withoutGlobalScope('tenant')->create(array_merge([
            'account_id' => $conversation->account_id,
            'conversation_id' => $conversation->id,
            'direction' => $direction,
            'channel' => $conversation->channel,
            'from_number' => $fromNumber,
            'to_number' => $toNumber,
            'content' => $content,
            'status' => $direction === 'inbound' ? 'received' : 'sent',
            'sent_at' => now(),
        ], $extra));

        // Update conversation denormalised fields
        $conversation->touchFromMessage($message);

        return $message;
    }

    /**
     * Mark conversation as read for a specific user.
     */
    public function markRead(string $conversationId, User $user): void
    {
        $conversation = InboxConversation::find($conversationId);
        if ($conversation) {
            $conversation->markRead($user);
        }
    }

    /**
     * Mark conversation as unread.
     */
    public function markUnread(string $conversationId): void
    {
        $conversation = InboxConversation::find($conversationId);
        if ($conversation) {
            $conversation->markUnread();
        }
    }

    /**
     * Get conversations updated since a timestamp (for polling).
     */
    public function getUpdatedSince(string $since): array
    {
        $conversations = InboxConversation::with('contact')
            ->active()
            ->where('updated_at', '>', $since)
            ->orderByDesc('last_message_at')
            ->limit(100)
            ->get();

        return $conversations->map(fn (InboxConversation $c) => $c->toPortalArray())
            ->values()
            ->all();
    }

    /**
     * Search within a conversation's messages.
     */
    public function searchMessages(string $conversationId, string $query): Collection
    {
        return InboxMessage::where('conversation_id', $conversationId)
            ->whereRaw("to_tsvector('english', COALESCE(content, '')) @@ plainto_tsquery('english', ?)", [$query])
            ->orderByDesc('sent_at')
            ->limit(50)
            ->get();
    }
}

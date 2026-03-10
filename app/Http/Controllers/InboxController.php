<?php

namespace App\Http\Controllers;

use App\Models\InboxConversation;
use App\Services\InboxDeliveryService;
use App\Services\InboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    private InboxService $inbox;
    private InboxDeliveryService $delivery;

    public function __construct(InboxService $inbox, InboxDeliveryService $delivery)
    {
        $this->inbox = $inbox;
        $this->delivery = $delivery;
    }

    /**
     * Inbox page — server renders the shell; JS hydrates.
     */
    public function index()
    {
        $conversations = $this->inbox->getConversationsArray();
        $unreadCount = $this->inbox->getUnreadCount();
        $senderIds = $this->getApprovedSenderIds();
        $rcsAgents = $this->getRcsAgentsForView();
        $templates = $this->getTemplatesForView();

        return view('quicksms.inbox.index', [
            'page_title'    => 'Inbox',
            'conversations' => $conversations,
            'unread_count'  => $unreadCount,
            'sender_ids'    => $senderIds,
            'rcs_agents'    => $rcsAgents,
            'templates'     => $templates,
        ]);
    }

    /* ── JSON API endpoints ─────────────────────────────────── */

    public function apiConversations(Request $request): JsonResponse
    {
        $filters = $request->only(['channel', 'unread_only', 'awaiting_reply', 'search', 'since']);

        return response()->json([
            'success' => true,
            'data'    => $this->inbox->getConversationsArray($filters),
        ]);
    }

    public function apiMessages(string $conversationId): JsonResponse
    {
        $conversation = $this->inbox->findConversation($conversationId);

        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        $contact = $conversation->contact;
        $phone = $conversation->phone_number;
        $masked = preg_replace('/(\+\d{2})\d{4}(\d{3})/', '$1 **** ***$2', $phone);
        $name = $contact
            ? trim($contact->first_name . ' ' . $contact->last_name)
            : $masked;
        $initials = $contact
            ? strtoupper(mb_substr($contact->first_name, 0, 1) . mb_substr($contact->last_name, 0, 1))
            : '??';

        return response()->json([
            'success' => true,
            'data'    => $conversation->messages->map(fn ($m) => $m->toPortalArray())->values(),
            'contact' => [
                'name'     => $name,
                'phone'    => $masked,
                'channel'  => $conversation->channel,
                'initials' => $initials,
            ],
        ]);
    }

    public function apiSendReply(Request $request, string $conversationId): JsonResponse
    {
        $user = auth()->user();
        if (!$user || !$user->canSendMessages()) {
            return response()->json(['success' => false, 'message' => 'You do not have permission to send messages'], 403);
        }

        $request->validate([
            'message'     => 'required_without:rcs_payload|string|max:1600',
            'channel'     => 'required|in:sms,rcs',
            'rcs_payload' => 'nullable|array',
            'sender_id'   => 'nullable|string|max:50',
        ]);

        $conversation = InboxConversation::find($conversationId);
        if (!$conversation) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        $result = $this->delivery->sendReply(
            $conversation,
            $request->input('message', ''),
            $request->input('channel', 'sms'),
            $request->input('rcs_payload'),
            $request->input('sender_id')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message']->toPortalArray(),
        ]);
    }

    public function apiMarkRead(string $conversationId): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $this->inbox->markRead($conversationId, $user);

        return response()->json(['success' => true]);
    }

    public function apiMarkUnread(string $conversationId): JsonResponse
    {
        $this->inbox->markUnread($conversationId);

        return response()->json(['success' => true]);
    }

    /**
     * Polling endpoint — returns conversations updated since a timestamp.
     */
    public function apiPoll(Request $request): JsonResponse
    {
        $request->validate([
            'since' => 'required|date',
        ]);

        $conversations = $this->inbox->getUpdatedSince($request->input('since'));
        $unreadCount = $this->inbox->getUnreadCount();

        return response()->json([
            'success'      => true,
            'data'         => $conversations,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Get total unread count (for dashboard tiles, nav badges).
     */
    public function apiUnreadCount(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'count'   => $this->inbox->getUnreadCount(),
        ]);
    }

    /* ── Private helpers (migrated from QuickSMSController) ── */

    private function getApprovedSenderIds(): array
    {
        $tenantId = session('customer_tenant_id');
        if (!$tenantId) {
            return [['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric']];
        }

        $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);
        $senderIds = \App\Models\SenderId::where('account_id', $tenantId)
            ->where('workflow_status', 'approved')
            ->orderByDesc('is_default')
            ->orderBy('sender_id_value')
            ->get();

        $result = [];
        if ($account && $account->isTestStandard()) {
            $result[] = ['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric'];
        }
        if ($senderIds->isEmpty() && empty($result)) {
            return [['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric']];
        }
        foreach ($senderIds as $s) {
            $result[] = [
                'id'   => $s->uuid,
                'name' => $s->sender_id_value,
                'type' => strtolower($s->sender_type === 'ALPHA' ? 'alphanumeric' : ($s->sender_type === 'NUMERIC' ? 'numeric' : 'shortcode')),
            ];
        }
        return $result;
    }

    private function getTemplatesForView(): array
    {
        $typeToChannel = [
            'sms'          => 'SMS',
            'rcs_basic'    => 'Basic RCS + SMS',
            'rcs_single'   => 'Rich RCS + SMS',
            'rcs_carousel' => 'Rich RCS + SMS',
        ];

        return \App\Models\MessageTemplate::whereIn('status', ['active', 'draft'])
            ->where(function ($q) {
                $q->where('trigger_type', 'portal')->orWhereNull('trigger_type');
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn ($t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'content'      => $t->content ?? '',
                'channel'      => $typeToChannel[$t->type] ?? 'SMS',
                'status'       => $t->status === 'active' ? 'Live' : ucfirst($t->status),
                'rcs_payload'  => $t->rcs_content,
                'sender_id'    => $t->sender_id_id,
                'rcs_agent_id' => $t->rcs_agent_id,
            ])
            ->toArray();
    }

    private function getRcsAgentsForView(): array
    {
        $userId = session('customer_user_id');
        $user = \App\Models\User::withoutGlobalScope('tenant')->find($userId);
        if (!$user) {
            return [];
        }

        if (!class_exists(\App\Models\RcsAgent::class)) {
            return [];
        }

        return \App\Models\RcsAgent::usableByUser($user)
            ->select('id', 'uuid', 'name', 'description', 'brand_color', 'logo_url')
            ->get()
            ->map(fn ($a) => [
                'id'          => $a->uuid,
                'name'        => $a->name,
                'logo'        => $a->logo_url ?: asset('images/rcs-agents/quicksms-brand.svg'),
                'tagline'     => $a->description ?? '',
                'brand_color' => $a->brand_color ?? '#886CC0',
            ])
            ->toArray();
    }
}

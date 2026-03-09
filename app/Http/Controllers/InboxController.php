<?php

namespace App\Http\Controllers;

use App\Services\InboxDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    private InboxDataService $data;

    public function __construct(InboxDataService $data)
    {
        $this->data = $data;
    }

    /**
     * Inbox page — server renders the shell; JS hydrates.
     */
    public function index()
    {
        $conversations = $this->data->getConversations();
        $unreadCount   = $this->data->getUnreadCount();
        $senderIds     = $this->getApprovedSenderIds();
        $rcsAgents     = $this->getRcsAgentsForView();
        $templates     = $this->getTemplatesForView();

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

    public function apiConversations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->data->getConversations(),
        ]);
    }

    public function apiMessages(string $conversationId): JsonResponse
    {
        $conv = $this->data->findConversation($conversationId);

        if (!$conv) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        return response()->json([
            'success'  => true,
            'data'     => $conv['messages'],
            'contact'  => [
                'name'     => $conv['name'],
                'phone'    => $conv['phone_masked'],
                'channel'  => $conv['channel'],
                'initials' => $conv['initials'],
            ],
        ]);
    }

    public function apiSendReply(Request $request, string $conversationId): JsonResponse
    {
        $request->validate([
            'message' => 'required_without:rcs_payload|string|max:1600',
            'channel' => 'required|in:sms,rcs',
            'rcs_payload' => 'nullable|array',
        ]);

        $conv = $this->data->findConversation($conversationId);
        if (!$conv) {
            return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
        }

        // TODO: Replace with actual message sending logic via API/queue
        return response()->json([
            'success' => true,
            'message' => [
                'direction' => 'outbound',
                'content'   => $request->input('message', ''),
                'channel'   => $request->input('channel', 'sms'),
                'time'      => now()->format('g:i A'),
                'date'      => now()->format('d M Y'),
            ],
        ]);
    }

    public function apiMarkRead(string $conversationId): JsonResponse
    {
        // TODO: Persist read status in database
        return response()->json(['success' => true]);
    }

    public function apiMarkUnread(string $conversationId): JsonResponse
    {
        // TODO: Persist unread status in database
        return response()->json(['success' => true]);
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
                'id'          => $t->id,
                'name'        => $t->name,
                'content'     => $t->content ?? '',
                'channel'     => $typeToChannel[$t->type] ?? 'SMS',
                'status'      => $t->status === 'active' ? 'Live' : ucfirst($t->status),
                'rcs_payload' => $t->rcs_content,
            ])
            ->toArray();
    }

    private function getRcsAgentsForView(): array
    {
        $userId = session('customer_user_id');
        $user = \App\Models\User::withoutGlobalScope('tenant')->find($userId);
        if (!$user) {
            return [
                ['id' => 'agent_1', 'name' => 'QuickSMS Brand', 'status' => 'approved'],
                ['id' => 'agent_2', 'name' => 'RetailBot', 'status' => 'approved'],
            ];
        }

        if (!class_exists(\App\Models\RcsAgent::class)) {
            return [
                ['id' => 'agent_1', 'name' => 'QuickSMS Brand', 'status' => 'approved'],
                ['id' => 'agent_2', 'name' => 'RetailBot', 'status' => 'approved'],
            ];
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

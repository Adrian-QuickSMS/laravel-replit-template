<?php

namespace App\Http\Controllers;

use App\Models\PlatformUpdate;
use App\Services\HubSpotHelpCentreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Customer-portal Help Centre dashboard endpoints.
 *
 * GREEN trust boundary: every endpoint runs under `customer.auth` middleware,
 * the user identity is taken from auth() server-side, and the only writes
 * (mark-read receipts) are scoped to the authenticated user's id.
 */
class HelpCentreController extends Controller
{
    private HubSpotHelpCentreService $hubspot;

    public function __construct(HubSpotHelpCentreService $hubspot)
    {
        $this->hubspot = $hubspot;
    }

    /**
     * GET /portal/api/help-centre/tickets
     */
    public function tickets(Request $request): JsonResponse
    {
        $user = $request->user();
        $email = $user?->email;
        $userKey = $user?->getKey();

        $counts = $this->hubspot->listOpenTicketsForEmail($email, $userKey);

        return response()->json([
            'success' => true,
            'data'    => $counts,
        ]);
    }

    /**
     * GET /portal/api/help-centre/kb/search?q=...
     */
    public function kbSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q'     => 'nullable|string|max:200',
            'limit' => 'nullable|integer|min:1|max:10',
        ]);

        $query = trim((string) ($validated['q'] ?? ''));
        $limit = (int) ($validated['limit'] ?? 5);

        $payload = $this->hubspot->searchKnowledgeBase($query, $limit);

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    /**
     * GET /portal/api/help-centre/platform-updates
     */
    public function platformUpdates(Request $request): JsonResponse
    {
        $userId = $request->user()?->getKey();

        $updates = PlatformUpdate::query()
            ->where('published', true)
            ->orderByDesc('posted_at')
            ->limit(50)
            ->get();

        $readIds = [];
        if ($userId && $updates->isNotEmpty()) {
            $readIds = DB::table('platform_update_reads')
                ->where('user_id', $userId)
                ->whereIn('platform_update_id', $updates->pluck('id'))
                ->pluck('platform_update_id')
                ->all();
        }
        $readSet = array_flip($readIds);

        $payload = $updates->map(function (PlatformUpdate $u) use ($readSet) {
            return [
                'id'        => $u->id,
                'type'      => $u->type,
                'title'     => $u->title,
                'body'      => $u->body,
                'posted_at' => optional($u->posted_at)->toIso8601String(),
                'link_url'  => $u->link_url,
                'is_read'   => isset($readSet[$u->id]),
            ];
        })->all();

        $unreadCount = collect($payload)->where('is_read', false)->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'updates'       => $payload,
                'unread_count'  => $unreadCount,
                'system_status' => 'operational', // operational | degraded | outage
                'checked_at'    => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * POST /portal/api/help-centre/platform-updates/mark-read
     *
     * Marks every published update as read for the authenticated user.
     * Idempotent — safe to call repeatedly.
     */
    public function markUpdatesRead(Request $request): JsonResponse
    {
        $userId = $request->user()?->getKey();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorised'], 401);
        }

        $now = now();
        $rows = PlatformUpdate::query()
            ->where('published', true)
            ->pluck('id')
            ->map(function ($id) use ($userId, $now) {
                return [
                    'platform_update_id' => $id,
                    'user_id'            => $userId,
                    'read_at'            => $now,
                ];
            })
            ->all();

        if (!empty($rows)) {
            DB::table('platform_update_reads')->upsert(
                $rows,
                ['platform_update_id', 'user_id'],
                ['read_at']
            );
        }

        return response()->json([
            'success' => true,
            'data'    => ['marked' => count($rows)],
        ]);
    }
}

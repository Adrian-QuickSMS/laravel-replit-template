<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\PlatformUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Admin Platform Updates Controller
 *
 * Lets internal staff create, edit, publish/unpublish and delete the
 * announcements that surface on the customer portal Help Centre dashboard.
 *
 * Route prefix: /admin/platform-updates (view)
 * API prefix:   /admin/api/platform-updates (JSON endpoints)
 *
 * All write actions write an immutable AdminAuditLog row.
 */
class PlatformUpdateAdminController extends Controller
{
    // =====================================================
    // VIEW ROUTE
    // =====================================================

    public function index()
    {
        return view('admin.platform-updates.index', [
            'page_title' => 'Platform Updates',
            'types'      => PlatformUpdate::TYPES,
        ]);
    }

    // =====================================================
    // API — LIST
    // =====================================================

    /**
     * GET /admin/api/platform-updates
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $updates = PlatformUpdate::query()
            ->withCount('reads')
            ->orderByDesc('posted_at')
            ->get();

        $now = now();
        $payload = $updates->map(function (PlatformUpdate $u) use ($now) {
            $isScheduled = (bool) $u->published
                && $u->posted_at
                && $u->posted_at->greaterThan($now);
            return [
                'id'           => $u->id,
                'type'         => $u->type,
                'title'        => $u->title,
                'body'         => $u->body,
                'link_url'     => $u->link_url,
                'published'    => (bool) $u->published,
                'posted_at'    => optional($u->posted_at)->toIso8601String(),
                'is_scheduled' => $isScheduled,
                'is_live'      => (bool) $u->published && !$isScheduled,
                'read_count'   => (int) ($u->reads_count ?? 0),
                'created_at'   => optional($u->created_at)->toIso8601String(),
                'updated_at'   => optional($u->updated_at)->toIso8601String(),
            ];
        })->all();

        return response()->json([
            'success' => true,
            'data'    => $payload,
        ]);
    }

    /**
     * GET /admin/api/platform-updates/{id}
     */
    public function apiShow(string $id): JsonResponse
    {
        $update = PlatformUpdate::query()->withCount('reads')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $update->id,
                'type'       => $update->type,
                'title'      => $update->title,
                'body'       => $update->body,
                'link_url'   => $update->link_url,
                'published'  => (bool) $update->published,
                'posted_at'  => optional($update->posted_at)->toIso8601String(),
                'read_count' => (int) ($update->reads_count ?? 0),
            ],
        ]);
    }

    // =====================================================
    // API — CREATE / UPDATE / DELETE
    // =====================================================

    /**
     * POST /admin/api/platform-updates
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        try {
            $update = PlatformUpdate::create([
                'type'      => $validated['type'],
                'title'     => $validated['title'],
                'body'      => $validated['body'],
                'link_url'  => $validated['link_url'] ?? null,
                'posted_at' => $validated['posted_at'] ?? now(),
                'published' => (bool) ($validated['published'] ?? true),
            ]);

            $this->audit(
                'PLATFORM_UPDATE_CREATED',
                'medium',
                $update,
                "Created platform update '{$update->title}' (type: {$update->type}).",
                [
                    'type'      => $update->type,
                    'title'     => $update->title,
                    'published' => (bool) $update->published,
                    'posted_at' => optional($update->posted_at)->toIso8601String(),
                    'link_url'  => $update->link_url,
                ]
            );

            return response()->json([
                'success' => true,
                'data'    => ['id' => $update->id],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Failed to create platform update', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => 'Failed to create platform update.',
            ], 500);
        }
    }

    /**
     * PUT /admin/api/platform-updates/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $update = PlatformUpdate::query()->findOrFail($id);
        $validated = $this->validatePayload($request);

        $before = [
            'type'      => $update->type,
            'title'     => $update->title,
            'body'      => $update->body,
            'link_url'  => $update->link_url,
            'posted_at' => optional($update->posted_at)->toIso8601String(),
            'published' => (bool) $update->published,
        ];

        try {
            // Match the UI hint: clearing posted_at means "post immediately"
            // (consistent with create behaviour).
            $update->fill([
                'type'      => $validated['type'],
                'title'     => $validated['title'],
                'body'      => $validated['body'],
                'link_url'  => $validated['link_url'] ?? null,
                'posted_at' => $validated['posted_at'] ?? now(),
                'published' => (bool) ($validated['published'] ?? $update->published),
            ])->save();

            $after = [
                'type'      => $update->type,
                'title'     => $update->title,
                'body'      => $update->body,
                'link_url'  => $update->link_url,
                'posted_at' => optional($update->posted_at)->toIso8601String(),
                'published' => (bool) $update->published,
            ];

            $this->audit(
                'PLATFORM_UPDATE_UPDATED',
                'medium',
                $update,
                "Edited platform update '{$update->title}'.",
                ['before' => $before, 'after' => $after]
            );

            return response()->json([
                'success' => true,
                'data'    => ['id' => $update->id],
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to update platform update', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'Failed to update platform update.',
            ], 500);
        }
    }

    /**
     * POST /admin/api/platform-updates/{id}/toggle-publish
     */
    public function togglePublish(Request $request, string $id): JsonResponse
    {
        $update = PlatformUpdate::query()->findOrFail($id);

        $previous = (bool) $update->published;
        $next     = !$previous;

        try {
            $update->forceFill(['published' => $next])->save();

            $action = $next ? 'PLATFORM_UPDATE_PUBLISHED' : 'PLATFORM_UPDATE_UNPUBLISHED';
            $verb   = $next ? 'Published' : 'Unpublished';

            $this->audit(
                $action,
                'medium',
                $update,
                "{$verb} platform update '{$update->title}'.",
                ['previous_published' => $previous, 'new_published' => $next]
            );

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'        => $update->id,
                    'published' => $next,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to toggle platform update publish state', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'Failed to change publish state.',
            ], 500);
        }
    }

    /**
     * DELETE /admin/api/platform-updates/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $update = PlatformUpdate::query()->findOrFail($id);

        $snapshot = [
            'id'        => $update->id,
            'type'      => $update->type,
            'title'     => $update->title,
            'published' => (bool) $update->published,
            'posted_at' => optional($update->posted_at)->toIso8601String(),
        ];

        try {
            $update->delete();

            $this->audit(
                'PLATFORM_UPDATE_DELETED',
                'high',
                null,
                "Deleted platform update '{$snapshot['title']}'.",
                $snapshot,
                $snapshot['id']
            );

            return response()->json([
                'success' => true,
                'data'    => ['id' => $snapshot['id']],
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to delete platform update', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error'   => 'Failed to delete platform update.',
            ], 500);
        }
    }

    // =====================================================
    // HELPERS
    // =====================================================

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'type'      => ['required', 'string', Rule::in(PlatformUpdate::TYPES)],
            'title'     => ['required', 'string', 'max:255'],
            'body'      => ['required', 'string', 'max:5000'],
            'link_url'  => ['nullable', 'url', 'max:500'],
            'posted_at' => ['nullable', 'date'],
            'published' => ['nullable', 'boolean'],
        ]);
    }

    private function audit(
        string $action,
        string $severity,
        ?PlatformUpdate $update,
        string $details,
        array $metadata = [],
        ?string $explicitTargetId = null
    ): void {
        $adminId   = session('admin_auth.admin_id', session('admin_user_id'));
        $adminName = session('admin_auth.name', session('admin_auth.email', session('admin_user_name', 'Admin')));

        AdminAuditLog::record(
            $action,
            'configuration',
            $severity,
            $adminId,
            $adminName,
            'platform_update',
            $explicitTargetId ?? ($update?->id),
            null,
            $details,
            $metadata
        );
    }
}

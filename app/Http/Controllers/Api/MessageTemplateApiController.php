<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Message Template API Controller
 *
 * JSON API for reusable SMS/RCS message templates:
 * - CRUD operations
 * - Encoding/segment calculation
 * - Merge field extraction
 * - Favourites management
 *
 * SECURITY: Tenant isolation via MessageTemplate global scope.
 */
class MessageTemplateApiController extends Controller
{
    private function tenantId(): string
    {
        return session('customer_tenant_id');
    }

    /**
     * List templates with filtering and search.
     */
    public function index(Request $request): JsonResponse
    {
        $query = MessageTemplate::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->ofType($request->input('type'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('favourites')) {
            $query->favourites();
        }
        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $templates = $query->orderByDesc('updated_at')->paginate($perPage);

        return response()->json([
            'data' => $templates->getCollection()->map(fn($t) => $t->toPortalArray()),
            'total' => $templates->total(),
            'per_page' => $templates->perPage(),
            'current_page' => $templates->currentPage(),
            'last_page' => $templates->lastPage(),
        ]);
    }

    /**
     * Get a single template.
     */
    public function show(string $id): JsonResponse
    {
        $template = MessageTemplate::find($id);

        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        return response()->json(['data' => $template->toPortalArray()]);
    }

    /**
     * Create a new template.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'required|string|in:sms,rcs_basic,rcs_single',
            'content' => 'nullable|string|max:10000',
            'rcs_content' => 'nullable|array',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'status' => 'nullable|string|in:draft,active',
        ]);

        $validated['account_id'] = $this->tenantId();
        $validated['status'] = $validated['status'] ?? 'draft';
        $validated['created_by'] = session('customer_email', session('customer_user_id'));

        $template = MessageTemplate::create($validated);

        // Auto-calculate encoding, segments, placeholders
        $template->recalculateMetadata();
        $template->save();

        return response()->json(['data' => $template->toPortalArray()], 201);
    }

    /**
     * Update a template.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $template = MessageTemplate::find($id);

        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'type' => 'sometimes|string|in:sms,rcs_basic,rcs_single',
            'content' => 'nullable|string|max:10000',
            'rcs_content' => 'nullable|array',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'status' => 'nullable|string|in:draft,active,archived',
        ]);

        $validated['updated_by'] = session('customer_email', session('customer_user_id'));

        $template->update($validated);

        // Recalculate if content changed
        if (isset($validated['content']) || isset($validated['type'])) {
            $template->recalculateMetadata();
            $template->save();
        }

        return response()->json(['data' => $template->toPortalArray()]);
    }

    /**
     * Delete a template (soft delete).
     */
    public function destroy(string $id): JsonResponse
    {
        $template = MessageTemplate::find($id);

        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $template->delete();

        return response()->json(['success' => true, 'message' => 'Template deleted']);
    }

    /**
     * Toggle favourite status.
     */
    public function toggleFavourite(string $id): JsonResponse
    {
        $template = MessageTemplate::find($id);

        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $template->update(['is_favourite' => !$template->is_favourite]);

        return response()->json([
            'success' => true,
            'is_favourite' => $template->is_favourite,
        ]);
    }

    /**
     * Calculate encoding, segment count, and detect placeholders for given text.
     * Stateless utility endpoint — does not create/modify any records.
     */
    public function analyseContent(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:10000',
        ]);

        $content = $request->input('content');
        $encoding = MessageTemplate::detectEncoding($content);
        $segments = MessageTemplate::calculateSegments($content, $encoding);
        $placeholders = MessageTemplate::extractPlaceholders($content);

        return response()->json([
            'encoding' => $encoding,
            'character_count' => mb_strlen($content),
            'segment_count' => $segments,
            'placeholders' => $placeholders,
        ]);
    }
}

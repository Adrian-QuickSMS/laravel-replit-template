<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Services\OptOutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Message Template API Controller
 *
 * JSON API for reusable SMS/RCS message templates:
 * - CRUD operations
 * - Encoding/segment calculation
 * - Merge field extraction
 * - Favourites management
 * - Opt-out configuration with keyword validation
 *
 * SECURITY: Tenant isolation via MessageTemplate global scope.
 */
class MessageTemplateApiController extends Controller
{
    public function __construct(
        private OptOutService $optOutService,
    ) {}

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
            'type' => 'required|string|in:sms,rcs_basic,rcs_single,rcs_carousel',
            'content' => 'nullable|string|max:10000',
            'rcs_content' => 'nullable|array',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'status' => 'nullable|string|in:draft,active',
            // Sender / RCS Agent
            'sender_id_id' => 'nullable|uuid',
            'rcs_agent_id' => 'nullable|uuid',
            // Opt-out configuration
            'opt_out_enabled' => 'nullable|boolean',
            'opt_out_method' => 'nullable|string|in:reply,url,both',
            'opt_out_number_id' => 'nullable|uuid',
            'opt_out_keyword' => 'nullable|string|min:4|max:10|regex:/^[A-Za-z0-9]+$/',
            'opt_out_text' => 'nullable|string|max:500',
            'opt_out_list_id' => 'nullable|uuid',
            'opt_out_url_enabled' => 'nullable|boolean',
            'opt_out_screening_list_ids' => 'nullable|array',
            'opt_out_screening_list_ids.*' => 'uuid',
            // Trackable link
            'trackable_link_enabled' => 'nullable|boolean',
            'trackable_link_domain' => 'nullable|string|max:255',
            // Message expiry
            'message_expiry_enabled' => 'nullable|boolean',
            'message_expiry_value' => 'nullable|string|max:10',
            // Social hours
            'social_hours_enabled' => 'nullable|boolean',
            'social_hours_from' => 'nullable|string|max:5',
            'social_hours_to' => 'nullable|string|max:5',
        ]);

        // Validate opt-out keyword if provided
        if (!empty($validated['opt_out_keyword']) && !empty($validated['opt_out_number_id'])) {
            try {
                $this->optOutService->validateOptOutKeyword(
                    $validated['opt_out_keyword'],
                    $validated['opt_out_number_id'],
                    $this->tenantId()
                );
            } catch (\RuntimeException $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
        }

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
            'type' => 'sometimes|string|in:sms,rcs_basic,rcs_single,rcs_carousel',
            'content' => 'nullable|string|max:10000',
            'rcs_content' => 'nullable|array',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'status' => 'nullable|string|in:draft,active,archived',
            // Sender / RCS Agent
            'sender_id_id' => 'nullable|uuid',
            'rcs_agent_id' => 'nullable|uuid',
            // Opt-out configuration
            'opt_out_enabled' => 'nullable|boolean',
            'opt_out_method' => 'nullable|string|in:reply,url,both',
            'opt_out_number_id' => 'nullable|uuid',
            'opt_out_keyword' => 'nullable|string|min:4|max:10|regex:/^[A-Za-z0-9]+$/',
            'opt_out_text' => 'nullable|string|max:500',
            'opt_out_list_id' => 'nullable|uuid',
            'opt_out_url_enabled' => 'nullable|boolean',
            'opt_out_screening_list_ids' => 'nullable|array',
            'opt_out_screening_list_ids.*' => 'uuid',
            // Trackable link
            'trackable_link_enabled' => 'nullable|boolean',
            'trackable_link_domain' => 'nullable|string|max:255',
            // Message expiry
            'message_expiry_enabled' => 'nullable|boolean',
            'message_expiry_value' => 'nullable|string|max:10',
            // Social hours
            'social_hours_enabled' => 'nullable|boolean',
            'social_hours_from' => 'nullable|string|max:5',
            'social_hours_to' => 'nullable|string|max:5',
        ]);

        // Validate opt-out keyword if changed
        $keyword = $validated['opt_out_keyword'] ?? $template->opt_out_keyword;
        $numberId = $validated['opt_out_number_id'] ?? $template->opt_out_number_id;
        if ($keyword && $numberId && (
            isset($validated['opt_out_keyword']) || isset($validated['opt_out_number_id'])
        )) {
            try {
                $this->optOutService->validateOptOutKeyword(
                    $keyword,
                    $numberId,
                    $this->tenantId()
                );
            } catch (\RuntimeException $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
        }

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

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use App\Models\MessageTemplateVersion;
use App\Models\MessageTemplateAuditLog;
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

        $validated['version'] = 1;
        $template = MessageTemplate::create($validated);

        $template->recalculateMetadata();
        $template->save();

        $this->createVersionSnapshot($template, 'Initial version');
        $this->createAuditEntry($template, 'created', 'Template created');

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
            'status' => 'nullable|string|in:draft,active,suspended,archived',
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

        $requestKeys = array_keys($request->except(['_token', '_method']));
        $isStatusOnlyChange = isset($validated['status']) && count($requestKeys) === 1 && $requestKeys[0] === 'status';

        if ($isStatusOnlyChange) {
            $oldStatus = $template->status;
            $newStatus = $validated['status'];
            $template->update($validated);

            $statusLabels = ['active' => 'live', 'suspended' => 'suspended', 'archived' => 'archived', 'draft' => 'draft'];
            $label = $statusLabels[$newStatus] ?? $newStatus;
            $this->createAuditEntry($template, $newStatus === 'archived' ? 'archived' : ($newStatus === 'suspended' ? 'suspended' : 'status-changed'), 'Status changed from ' . ($statusLabels[$oldStatus] ?? $oldStatus) . ' to ' . $label);
        } else {
            $this->createVersionSnapshot($template, 'Version before edit');

            $newVersion = ($template->version ?? 1) + 1;
            $validated['version'] = $newVersion;

            $template->update($validated);

            if (isset($validated['content']) || isset($validated['type'])) {
                $template->recalculateMetadata();
                $template->save();
            }

            $this->createVersionSnapshot($template, 'Edited');
            $this->createAuditEntry($template, 'edited', 'Template updated to v' . $newVersion);
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

    public function versionHistory(string $id): JsonResponse
    {
        $template = MessageTemplate::find($id);
        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $versions = MessageTemplateVersion::where('template_id', $id)
            ->orderByDesc('version')
            ->get()
            ->map(fn($v) => $v->toPortalArray());

        return response()->json(['data' => $versions]);
    }

    public function auditLog(string $id): JsonResponse
    {
        $template = MessageTemplate::find($id);
        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $entries = MessageTemplateAuditLog::where('template_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($e) => $e->toPortalArray());

        return response()->json(['data' => $entries]);
    }

    public function rollback(string $id, int $version): JsonResponse
    {
        $template = MessageTemplate::find($id);
        if (!$template) {
            return response()->json(['status' => 'error', 'message' => 'Template not found'], 404);
        }

        $targetVersion = MessageTemplateVersion::where('template_id', $id)
            ->where('version', $version)
            ->first();

        if (!$targetVersion) {
            return response()->json(['status' => 'error', 'message' => 'Version not found'], 404);
        }

        $this->createVersionSnapshot($template, 'Version before rollback');

        $snapshot = $targetVersion->snapshot;
        $newVersion = ($template->version ?? 1) + 1;

        $rollbackFields = array_intersect_key($snapshot, array_flip([
            'name', 'description', 'type', 'content', 'rcs_content',
            'encoding', 'character_count', 'segment_count', 'status',
            'sender_id_id', 'rcs_agent_id',
            'opt_out_enabled', 'opt_out_method', 'opt_out_text',
            'trackable_link_enabled', 'message_expiry_enabled',
            'social_hours_enabled',
        ]));

        $rollbackFields['version'] = $newVersion;
        $rollbackFields['updated_by'] = session('customer_email', session('customer_user_id'));
        $template->update($rollbackFields);

        $this->createVersionSnapshot($template, 'Rolled back from v' . $version);
        $this->createAuditEntry($template, 'rolled-back', 'Rolled back to version ' . $version . ', creating v' . $newVersion);

        return response()->json(['data' => $template->toPortalArray()]);
    }

    private function createVersionSnapshot(MessageTemplate $template, string $changeNote = ''): void
    {
        $editedBy = session('customer_email', session('customer_user_id', 'System'));

        $existing = MessageTemplateVersion::withoutGlobalScopes()
            ->where('template_id', $template->id)
            ->where('version', $template->version)
            ->exists();
        if ($existing) {
            return;
        }

        MessageTemplateVersion::withoutGlobalScopes()->create([
            'template_id' => $template->id,
            'account_id' => $template->account_id,
            'version' => $template->version,
            'snapshot' => [
                'name' => $template->name,
                'description' => $template->description,
                'type' => $template->type,
                'content' => $template->content,
                'rcs_content' => $template->rcs_content,
                'encoding' => $template->encoding,
                'character_count' => $template->character_count,
                'segment_count' => $template->segment_count,
                'status' => $template->status,
                'sender_id_id' => $template->sender_id_id,
                'rcs_agent_id' => $template->rcs_agent_id,
                'opt_out_enabled' => $template->opt_out_enabled,
                'opt_out_method' => $template->opt_out_method,
                'opt_out_text' => $template->opt_out_text,
                'trackable_link_enabled' => $template->trackable_link_enabled,
                'message_expiry_enabled' => $template->message_expiry_enabled,
                'social_hours_enabled' => $template->social_hours_enabled,
            ],
            'change_note' => $changeNote,
            'edited_by' => $editedBy,
            'created_at' => now(),
        ]);
    }

    private function createAuditEntry(MessageTemplate $template, string $action, string $details = ''): void
    {
        $userId = session('customer_user_id', 'system');
        $userName = session('customer_email', 'System');

        MessageTemplateAuditLog::withoutGlobalScopes()->create([
            'template_id' => $template->id,
            'account_id' => $template->account_id,
            'action' => $action,
            'version' => $template->version,
            'user_id' => $userId,
            'user_name' => $userName,
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}

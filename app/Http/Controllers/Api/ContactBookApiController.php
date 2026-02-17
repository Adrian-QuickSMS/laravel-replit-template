<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\ContactTimelineEvent;
use App\Models\OptOutList;
use App\Models\OptOutRecord;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Contact Book API Controller
 *
 * Handles all JSON API operations for the Contact Book module:
 * - Contact CRUD + search
 * - Bulk operations (tags, lists, delete, export)
 * - Tag CRUD
 * - List CRUD + member management
 * - Opt-out list CRUD + record management
 * - Timeline retrieval
 *
 * SECURITY: All methods rely on two layers of tenant isolation:
 * 1. Eloquent global scopes (session-based tenant filtering)
 * 2. PostgreSQL RLS (fail-closed — zero rows if tenant not set)
 */
class ContactBookApiController extends Controller
{
    /**
     * Resolve the current tenant ID from session.
     */
    private function tenantId(): string
    {
        return session('customer_tenant_id');
    }

    // =====================================================
    // CONTACTS
    // =====================================================

    public function contactsIndex(Request $request): JsonResponse
    {
        $query = Contact::with(['tags', 'lists']);

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('status')) {
            $query->whereRaw("status = ?", [$request->input('status')]);
        }
        if ($request->filled('source')) {
            $query->whereRaw("source = ?", [$request->input('source')]);
        }
        if ($request->filled('sub_account_id')) {
            $query->forSubAccount($request->input('sub_account_id'));
        }
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('name', $request->input('tag')));
        }
        if ($request->filled('list')) {
            $query->whereHas('lists', fn($q) => $q->where('name', $request->input('list')));
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $contacts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $contacts->getCollection()->map(fn($c) => $c->toPortalArray()),
            'total' => $contacts->total(),
            'per_page' => $contacts->perPage(),
            'current_page' => $contacts->currentPage(),
            'last_page' => $contacts->lastPage(),
        ]);
    }

    public function contactsShow(string $id): JsonResponse
    {
        $contact = Contact::with(['tags', 'lists'])->find($id);

        if (!$contact) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

        $data = $contact->toPortalArray();
        $data['tags'] = $contact->tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color])->toArray();
        $data['lists'] = $contact->lists->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->toArray();
        $data['custom_data'] = $contact->custom_data;
        $data['date_of_birth'] = $contact->date_of_birth?->format('Y-m-d');
        $data['postcode'] = $contact->postcode;
        $data['city'] = $contact->city;
        $data['country'] = $contact->country;
        $data['mobile_number'] = $contact->mobile_number;

        return response()->json(['data' => $data]);
    }

    public function contactsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobile_number' => 'required|string|max:20',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'postcode' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|size:2',
            'sub_account_id' => 'nullable|uuid',
            'custom_data' => 'nullable|array',
        ]);

        $validated['account_id'] = $this->tenantId();
        $validated['created_by'] = session('customer_email', session('customer_user_id'));

        $contact = Contact::create($validated);
        $contact->load(['tags', 'lists']);

        return response()->json(['data' => $contact->toPortalArray()], 201);
    }

    public function contactsUpdate(Request $request, string $id): JsonResponse
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

        $validated = $request->validate([
            'mobile_number' => 'sometimes|string|max:20',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'date_of_birth' => 'nullable|date',
            'postcode' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|size:2',
            'sub_account_id' => 'nullable|uuid',
            'custom_data' => 'nullable|array',
        ]);

        $validated['updated_by'] = session('customer_email', session('customer_user_id'));

        $contact->update($validated);
        $contact->load(['tags', 'lists']);

        return response()->json(['data' => $contact->toPortalArray()]);
    }

    public function contactsDestroy(string $id): JsonResponse
    {
        $contact = Contact::find($id);

        if (!$contact) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

        // Soft-delete via Eloquent (preserves record with deleted_at timestamp)
        $contact->delete();

        return response()->json(['success' => true, 'message' => 'Contact deleted']);
    }

    // =====================================================
    // BULK OPERATIONS
    // =====================================================

    public function bulkAddToList(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
            'list_name' => 'required|string|max:255',
        ]);

        $list = ContactList::where('name', $request->input('list_name'))->firstOrFail();

        // Verify contact_ids belong to current tenant via global scope
        $contactIds = Contact::whereIn('id', $request->input('contact_ids'))->pluck('id');

        $inserted = 0;
        DB::transaction(function () use ($contactIds, $list, &$inserted) {
            foreach ($contactIds as $contactId) {
                $wasInserted = DB::statement(
                    "INSERT INTO contact_list_member (contact_id, list_id, created_at) VALUES (?, ?, NOW()) ON CONFLICT DO NOTHING",
                    [$contactId, $list->id]
                );
                // ON CONFLICT DO NOTHING returns true but doesn't tell us if row was inserted
                // Count via affected rows is not reliable with ON CONFLICT, so count after
            }
            $inserted = DB::table('contact_list_member')
                ->where('list_id', $list->id)
                ->whereIn('contact_id', $contactIds)
                ->count();
        });

        $list->refreshContactCount();

        return response()->json([
            'success' => true,
            'message' => "Added contact(s) to \"{$list->name}\"",
            'affectedCount' => $inserted,
        ]);
    }

    public function bulkRemoveFromList(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
            'list_name' => 'required|string|max:255',
        ]);

        $list = ContactList::where('name', $request->input('list_name'))->firstOrFail();

        $deleted = DB::table('contact_list_member')
            ->where('list_id', $list->id)
            ->whereIn('contact_id', $request->input('contact_ids'))
            ->delete();

        $list->refreshContactCount();

        return response()->json([
            'success' => true,
            'message' => "Removed {$deleted} contact(s) from \"{$list->name}\"",
            'affectedCount' => $deleted,
        ]);
    }

    public function bulkAddTags(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:100',
        ]);

        $accountId = $this->tenantId();
        // Verify contact_ids belong to current tenant via global scope
        $contactIds = Contact::whereIn('id', $request->input('contact_ids'))->pluck('id');
        $affectedCount = 0;

        DB::transaction(function () use ($request, $accountId, $contactIds, &$affectedCount) {
            foreach ($request->input('tags') as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['account_id' => $accountId, 'name' => $tagName],
                    ['color' => '#6366f1']
                );

                foreach ($contactIds as $contactId) {
                    DB::statement(
                        "INSERT INTO contact_tag (contact_id, tag_id, created_at) VALUES (?, ?, NOW()) ON CONFLICT DO NOTHING",
                        [$contactId, $tag->id]
                    );
                }

                $tag->update(['last_used' => now()]);
                $tag->refreshContactCount();
            }
        });

        $tagNames = implode(', ', $request->input('tags'));

        return response()->json([
            'success' => true,
            'message' => "Added tag(s) \"{$tagNames}\" to {$contactIds->count()} contact(s)",
            'affectedCount' => $contactIds->count(),
        ]);
    }

    public function bulkRemoveTags(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:100',
        ]);

        // Verify contact_ids belong to current tenant via global scope
        $verifiedContactIds = Contact::whereIn('id', $request->input('contact_ids'))->pluck('id');

        $tags = Tag::whereIn('name', $request->input('tags'))->get();
        $tagIds = $tags->pluck('id');

        $deleted = DB::table('contact_tag')
            ->whereIn('contact_id', $verifiedContactIds)
            ->whereIn('tag_id', $tagIds)
            ->delete();

        // Batch refresh counts
        foreach ($tags as $tag) {
            $tag->refreshContactCount();
        }

        $tagNames = implode(', ', $request->input('tags'));

        return response()->json([
            'success' => true,
            'message' => "Removed tag(s) \"{$tagNames}\" from selected contacts",
            'affectedCount' => $deleted,
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
        ]);

        // Eloquent soft-delete — global scope ensures tenant isolation
        $deleted = Contact::whereIn('id', $request->input('contact_ids'))->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} contact(s)",
            'affectedCount' => $deleted,
        ]);
    }

    public function bulkExport(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
            'fields' => 'nullable|array',
            'format' => 'nullable|string|in:csv,xlsx',
        ]);

        // Verify contact_ids belong to current tenant
        $count = Contact::whereIn('id', $request->input('contact_ids'))->count();

        return response()->json([
            'success' => true,
            'message' => "Export of {$count} contact(s) initiated",
            'affectedCount' => $count,
        ]);
    }

    // =====================================================
    // TAGS
    // =====================================================

    public function tagsIndex(): JsonResponse
    {
        $tags = Tag::orderBy('name')->get();
        return response()->json(['data' => $tags->map(fn($t) => $t->toPortalArray())]);
    }

    public function tagsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $validated['account_id'] = $this->tenantId();

        $tag = Tag::create($validated);
        return response()->json(['data' => $tag->toPortalArray()], 201);
    }

    public function tagsUpdate(Request $request, string $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['status' => 'error', 'message' => 'Tag not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $tag->update($validated);

        return response()->json(['data' => $tag->toPortalArray()]);
    }

    public function tagsDestroy(string $id): JsonResponse
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['status' => 'error', 'message' => 'Tag not found'], 404);
        }

        DB::transaction(function () use ($tag) {
            DB::table('contact_tag')->where('tag_id', $tag->id)->delete();
            $tag->delete();
        });

        return response()->json(['success' => true, 'message' => 'Tag deleted']);
    }

    // =====================================================
    // LISTS
    // =====================================================

    public function listsIndex(): JsonResponse
    {
        $lists = ContactList::orderBy('name')->get();
        return response()->json(['data' => $lists->map(fn($l) => $l->toPortalArray())]);
    }

    public function listsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'rules' => 'nullable|array',
            'contact_ids' => 'nullable|array',
            'contact_ids.*' => 'uuid',
        ]);

        $accountId = $this->tenantId();
        $validated['account_id'] = $accountId;

        $existing = ContactList::where('name', $validated['name'])->first();
        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'A list with this name already exists. Please choose a different name.'], 422);
        }

        $list = DB::transaction(function () use ($validated, $request) {
            $listId = (string) Str::uuid();
            $validated['id'] = $listId;

            $list = ContactList::create($validated);

            if ($request->filled('rules')) {
                DB::statement("UPDATE contact_lists SET type = 'dynamic' WHERE id = ?", [$listId]);
            }

            if (!empty($validated['contact_ids'])) {
                // Verify contact_ids belong to current tenant
                $verifiedIds = Contact::whereIn('id', $validated['contact_ids'])->pluck('id');

                foreach ($verifiedIds as $contactId) {
                    DB::statement(
                        "INSERT INTO contact_list_member (contact_id, list_id, created_at) VALUES (?, ?, NOW()) ON CONFLICT DO NOTHING",
                        [$contactId, $listId]
                    );
                }

                $list->update([
                    'contact_count' => DB::table('contact_list_member')->where('list_id', $listId)->count(),
                ]);
            }

            return $list->fresh();
        });

        return response()->json(['data' => $list->toPortalArray()], 201);
    }

    public function listsUpdate(Request $request, string $id): JsonResponse
    {
        $list = ContactList::find($id);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'List not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'rules' => 'nullable|array',
        ]);

        $list->update($validated);

        return response()->json(['data' => $list->toPortalArray()]);
    }

    public function listsDestroy(string $id): JsonResponse
    {
        $list = ContactList::find($id);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'List not found'], 404);
        }

        DB::transaction(function () use ($list) {
            DB::table('contact_list_member')->where('list_id', $list->id)->delete();
            $list->delete();
        });

        return response()->json(['success' => true, 'message' => 'List deleted']);
    }

    public function listsAddMembers(Request $request, string $id): JsonResponse
    {
        $list = ContactList::find($id);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'List not found'], 404);
        }

        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
        ]);

        // Verify contact_ids belong to current tenant
        $verifiedIds = Contact::whereIn('id', $request->input('contact_ids'))->pluck('id');

        DB::transaction(function () use ($verifiedIds, $list) {
            foreach ($verifiedIds as $contactId) {
                DB::statement(
                    "INSERT INTO contact_list_member (contact_id, list_id, created_at) VALUES (?, ?, NOW()) ON CONFLICT DO NOTHING",
                    [$contactId, $list->id]
                );
            }

            $list->update([
                'contact_count' => DB::table('contact_list_member')->where('list_id', $list->id)->count(),
            ]);
        });

        return response()->json(['success' => true, 'added' => $verifiedIds->count()]);
    }

    public function listsRemoveMembers(Request $request, string $id): JsonResponse
    {
        $list = ContactList::find($id);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'List not found'], 404);
        }

        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
        ]);

        // Verify contact_ids belong to current tenant
        $verifiedIds = Contact::whereIn('id', $request->input('contact_ids'))->pluck('id');

        $deleted = DB::table('contact_list_member')
            ->where('list_id', $list->id)
            ->whereIn('contact_id', $verifiedIds)
            ->delete();

        $list->update([
            'contact_count' => DB::table('contact_list_member')->where('list_id', $list->id)->count(),
        ]);

        return response()->json(['success' => true, 'removed' => $deleted]);
    }

    // =====================================================
    // OPT-OUT LISTS
    // =====================================================

    public function optOutListsIndex(): JsonResponse
    {
        $lists = OptOutList::orderByDesc('is_master')->orderBy('name')->get();
        return response()->json(['data' => $lists->map(fn($l) => $l->toPortalArray())]);
    }

    public function optOutListsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // is_master is NOT user-settable — only system can create master lists
        $validated['account_id'] = $this->tenantId();

        $list = OptOutList::create($validated);
        return response()->json(['data' => $list->toPortalArray()], 201);
    }

    public function optOutListsUpdate(Request $request, string $id): JsonResponse
    {
        $list = OptOutList::find($id);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $list->update($validated);

        return response()->json(['data' => $list->toPortalArray()]);
    }

    public function optOutListsDestroy(string $id): JsonResponse
    {
        $list = OptOutList::find($id);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        if ($list->is_master) {
            return response()->json(['error' => 'Cannot delete the master opt-out list'], 422);
        }

        DB::transaction(function () use ($list) {
            DB::table('opt_out_records')->where('opt_out_list_id', $list->id)->delete();
            $list->delete();
        });

        return response()->json(['success' => true, 'message' => 'Opt-out list deleted']);
    }

    public function optOutRecordsIndex(Request $request, string $listId): JsonResponse
    {
        $list = OptOutList::find($listId);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $records = OptOutRecord::where('opt_out_list_id', $listId)
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'data' => $records->getCollection()->map(fn($r) => [
                'id' => $r->id,
                'mobile_number' => $r->mobile_number,
                'source' => $r->getRawOriginal('source') ?? 'manual',
                'campaign_ref' => $r->campaign_ref,
                'opt_out_list_id' => $r->opt_out_list_id,
                'created_at' => $r->created_at,
            ]),
            'total' => $records->total(),
        ]);
    }

    public function optOutRecordsStore(Request $request, string $listId): JsonResponse
    {
        $list = OptOutList::find($listId);

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        $validated = $request->validate([
            'mobile_number' => 'required|string|max:20',
            'campaign_ref' => 'nullable|string|max:255',
            'source' => 'nullable|string|in:manual,api,import,inbound_reply',
        ]);

        $recordId = (string) Str::uuid();
        DB::table('opt_out_records')->insert([
            'id' => $recordId,
            'mobile_number' => $validated['mobile_number'],
            'campaign_ref' => $validated['campaign_ref'] ?? null,
            'account_id' => $this->tenantId(),
            'opt_out_list_id' => $listId,
            'source' => $validated['source'] ?? 'manual',
            'created_at' => now(),
        ]);

        $list->refreshCount();

        $record = DB::table('opt_out_records')->where('id', $recordId)->first();
        return response()->json(['data' => [
            'id' => $record->id,
            'mobile_number' => $record->mobile_number,
            'source' => $record->source ?? 'manual',
            'campaign_ref' => $record->campaign_ref,
            'opt_out_list_id' => $record->opt_out_list_id,
            'created_at' => $record->created_at,
        ]], 201);
    }

    public function optOutRecordsDestroy(string $id): JsonResponse
    {
        $accountId = $this->tenantId();
        $record = DB::table('opt_out_records')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->first();

        if (!$record) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out record not found'], 404);
        }

        $listId = $record->opt_out_list_id;
        DB::table('opt_out_records')->where('id', $id)->delete();

        $newCount = DB::table('opt_out_records')->where('opt_out_list_id', $listId)->count();
        DB::table('opt_out_lists')->where('id', $listId)->update(['count' => $newCount]);

        return response()->json(['success' => true, 'message' => 'Opt-out record removed']);
    }

    // =====================================================
    // TIMELINE
    // =====================================================

    public function timeline(Request $request, string $contactId): JsonResponse
    {
        // Verify contact exists and belongs to current tenant
        $contact = Contact::find($contactId);

        if (!$contact) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

        $query = ContactTimelineEvent::forContact($contactId)
            ->orderByDesc('created_at');

        if ($request->filled('event_types')) {
            $types = explode(',', $request->input('event_types'));
            $query->whereIn('event_type', $types);
        }
        if ($request->filled('sources')) {
            $sources = explode(',', $request->input('sources'));
            $query->whereIn('source_module', $sources);
        }
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query->dateRange($request->input('date_from'), $request->input('date_to'));
        }

        $limit = min((int) $request->input('limit', 50), 100);
        $cursor = $request->input('cursor');

        if ($cursor) {
            $cursorEvent = ContactTimelineEvent::where('event_id', $cursor)->first();
            if ($cursorEvent) {
                $query->where('created_at', '<', $cursorEvent->created_at);
            }
        }

        $events = $query->limit($limit + 1)->get();
        $hasMore = $events->count() > $limit;
        $pageEvents = $events->take($limit);
        $nextCursor = $hasMore && $pageEvents->isNotEmpty()
            ? $pageEvents->last()->event_id
            : null;

        return response()->json([
            'events' => $pageEvents->map(fn($e) => $e->toPortalArray()),
            'returned' => $pageEvents->count(),
            'cursor' => $nextCursor,
            'hasMore' => $hasMore,
        ]);
    }

    public function revealMsisdn(Request $request, string $contactId): JsonResponse
    {
        $contact = Contact::find($contactId);

        if (!$contact) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        Log::info('MSISDN revealed', [
            'contact_id' => $contactId,
            'account_id' => $this->tenantId(),
            'user_id' => session('customer_user_id'),
            'reason' => $request->input('reason', 'User requested reveal'),
        ]);

        return response()->json([
            'success' => true,
            'msisdn' => $contact->mobile_number,
            'revealed_at' => now()->toIso8601String(),
        ]);
    }
}

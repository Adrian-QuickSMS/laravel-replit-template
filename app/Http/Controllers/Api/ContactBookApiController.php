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
 */
class ContactBookApiController extends Controller
{
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

        $contacts = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 25));

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
        $contact = Contact::with(['tags', 'lists'])->findOrFail($id);
        return response()->json(['data' => $contact->toPortalArray()]);
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

        $validated['account_id'] = session('customer_tenant_id');
        $validated['created_by'] = session('customer_email', session('customer_user_id'));

        $contact = Contact::create($validated);
        $contact->load(['tags', 'lists']);

        return response()->json(['data' => $contact->toPortalArray()], 201);
    }

    public function contactsUpdate(Request $request, string $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

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
        $contact = Contact::findOrFail($id);
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
            'list_name' => 'required|string',
        ]);

        $accountId = session('customer_tenant_id');
        $list = ContactList::where('name', $request->input('list_name'))->firstOrFail();

        $contactIds = Contact::whereIn('id', $request->input('contact_ids'))->pluck('id');

        $inserted = 0;
        foreach ($contactIds as $contactId) {
            $exists = DB::table('contact_list_member')
                ->where('contact_id', $contactId)
                ->where('list_id', $list->id)
                ->exists();

            if (!$exists) {
                DB::table('contact_list_member')->insert([
                    'contact_id' => $contactId,
                    'list_id' => $list->id,
                    'created_at' => now(),
                ]);
                $inserted++;
            }
        }

        $list->refreshContactCount();

        return response()->json([
            'success' => true,
            'message' => "Added {$inserted} contact(s) to \"{$list->name}\"",
            'affectedCount' => $inserted,
        ]);
    }

    public function bulkRemoveFromList(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
            'list_name' => 'required|string',
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
            'tags.*' => 'string',
        ]);

        $accountId = session('customer_tenant_id');
        $contactIds = Contact::whereIn('id', $request->input('contact_ids'))->pluck('id');
        $affectedCount = 0;

        foreach ($request->input('tags') as $tagName) {
            $tag = Tag::firstOrCreate(
                ['account_id' => $accountId, 'name' => $tagName],
                ['color' => '#6366f1']
            );

            foreach ($contactIds as $contactId) {
                $exists = DB::table('contact_tag')
                    ->where('contact_id', $contactId)
                    ->where('tag_id', $tag->id)
                    ->exists();

                if (!$exists) {
                    DB::table('contact_tag')->insert([
                        'contact_id' => $contactId,
                        'tag_id' => $tag->id,
                        'created_at' => now(),
                    ]);
                    $affectedCount++;
                }
            }

            $tag->update(['last_used' => now()]);
            $tag->refreshContactCount();
        }

        $tagNames = implode(', ', $request->input('tags'));

        return response()->json([
            'success' => true,
            'message' => "Added tag(s) \"{$tagNames}\" to {$contactIds->count()} contact(s)",
            'affectedCount' => $affectedCount,
        ]);
    }

    public function bulkRemoveTags(Request $request): JsonResponse
    {
        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
            'tags' => 'required|array|min:1',
            'tags.*' => 'string',
        ]);

        $tagIds = Tag::whereIn('name', $request->input('tags'))->pluck('id');
        $deleted = DB::table('contact_tag')
            ->whereIn('contact_id', $request->input('contact_ids'))
            ->whereIn('tag_id', $tagIds)
            ->delete();

        Tag::whereIn('id', $tagIds)->each(fn($tag) => $tag->refreshContactCount());

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

        // For now, return a placeholder — actual export generation would be a queued job
        return response()->json([
            'success' => true,
            'message' => 'Export of ' . count($request->input('contact_ids')) . ' contact(s) initiated',
            'affectedCount' => count($request->input('contact_ids')),
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

        $validated['account_id'] = session('customer_tenant_id');

        $tag = Tag::create($validated);
        return response()->json(['data' => $tag->toPortalArray()], 201);
    }

    public function tagsUpdate(Request $request, string $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        $tag->update($request->validate([
            'name' => 'sometimes|string|max:100',
            'color' => 'nullable|string|max:7',
        ]));
        return response()->json(['data' => $tag->toPortalArray()]);
    }

    public function tagsDestroy(string $id): JsonResponse
    {
        $tag = Tag::findOrFail($id);
        DB::table('contact_tag')->where('tag_id', $id)->delete();
        $tag->delete();
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
            'description' => 'nullable|string',
            'rules' => 'nullable|array',
        ]);

        $validated['account_id'] = session('customer_tenant_id');

        $existing = ContactList::where('name', $validated['name'])->first();
        if ($existing) {
            return response()->json(['status' => 'error', 'message' => 'A list with this name already exists. Please choose a different name.'], 422);
        }

        $listId = (string) \Illuminate\Support\Str::uuid();
        $validated['id'] = $listId;

        $list = ContactList::create($validated);

        if ($request->filled('rules')) {
            DB::statement("UPDATE contact_lists SET type = 'dynamic' WHERE id = ?", [$listId]);
        }

        if ($request->filled('contact_ids') && is_array($request->input('contact_ids'))) {
            foreach ($request->input('contact_ids') as $contactId) {
                $memberExists = DB::table('contact_list_member')
                    ->where('contact_id', $contactId)
                    ->where('list_id', $listId)
                    ->exists();
                if (!$memberExists) {
                    DB::table('contact_list_member')->insert([
                        'contact_id' => $contactId,
                        'list_id' => $listId,
                        'created_at' => now(),
                    ]);
                }
            }
            DB::table('contact_lists')
                ->where('id', $listId)
                ->update(['contact_count' => DB::table('contact_list_member')->where('list_id', $listId)->count()]);
        }

        $freshList = ContactList::withoutGlobalScopes()->find($listId);
        return response()->json(['data' => ($freshList ? $freshList->toPortalArray() : $list->toPortalArray())], 201);
    }

    public function listsUpdate(Request $request, string $id): JsonResponse
    {
        $list = ContactList::findOrFail($id);
        $list->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|array',
        ]));
        return response()->json(['data' => $list->toPortalArray()]);
    }

    public function listsDestroy(string $id): JsonResponse
    {
        $list = ContactList::findOrFail($id);
        DB::table('contact_list_member')->where('list_id', $id)->delete();
        $list->delete();
        return response()->json(['success' => true, 'message' => 'List deleted']);
    }

    public function listsAddMembers(Request $request, string $id): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $listExists = DB::table('contact_lists')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$listExists) {
            return response()->json(['status' => 'error', 'message' => 'List not found'], 404);
        }

        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'string',
        ]);

        $inserted = 0;
        foreach ($request->input('contact_ids') as $contactId) {
            $exists = DB::table('contact_list_member')
                ->where('contact_id', $contactId)
                ->where('list_id', $id)
                ->exists();

            if (!$exists) {
                DB::table('contact_list_member')->insert([
                    'contact_id' => $contactId,
                    'list_id' => $id,
                    'created_at' => now(),
                ]);
                $inserted++;
            }
        }

        DB::table('contact_lists')
            ->where('id', $id)
            ->update(['contact_count' => DB::table('contact_list_member')->where('list_id', $id)->count()]);

        return response()->json(['success' => true, 'added' => $inserted]);
    }

    public function listsRemoveMembers(Request $request, string $id): JsonResponse
    {
        $list = ContactList::findOrFail($id);

        $request->validate([
            'contact_ids' => 'required|array|min:1',
            'contact_ids.*' => 'uuid',
        ]);

        $deleted = DB::table('contact_list_member')
            ->where('list_id', $list->id)
            ->whereIn('contact_id', $request->input('contact_ids'))
            ->delete();

        $list->refreshContactCount();
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
            'description' => 'nullable|string',
            'is_master' => 'nullable|boolean',
        ]);

        $validated['account_id'] = session('customer_tenant_id');

        $list = OptOutList::create($validated);
        return response()->json(['data' => $list->toPortalArray()], 201);
    }

    public function optOutListsUpdate(Request $request, string $id): JsonResponse
    {
        $list = OptOutList::findOrFail($id);
        $list->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]));
        return response()->json(['data' => $list->toPortalArray()]);
    }

    public function optOutListsDestroy(string $id): JsonResponse
    {
        $list = OptOutList::findOrFail($id);
        if ($list->is_master) {
            return response()->json(['error' => 'Cannot delete the master opt-out list'], 422);
        }
        $list->records()->delete();
        $list->delete();
        return response()->json(['success' => true, 'message' => 'Opt-out list deleted']);
    }

    public function optOutRecordsIndex(Request $request, string $listId): JsonResponse
    {
        $list = OptOutList::findOrFail($listId);

        $records = $list->records()
            ->with('optOutList')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => $records->getCollection()->map(fn($r) => $r->toPortalArray()),
            'total' => $records->total(),
        ]);
    }

    public function optOutRecordsStore(Request $request, string $listId): JsonResponse
    {
        $list = OptOutList::findOrFail($listId);

        $validated = $request->validate([
            'mobile_number' => 'required|string|max:20',
            'campaign_ref' => 'nullable|string|max:255',
        ]);

        $validated['account_id'] = session('customer_tenant_id');
        $validated['opt_out_list_id'] = $list->id;

        $record = OptOutRecord::create($validated);
        // Source is set via DB default ('manual'), update if provided
        if ($request->filled('source')) {
            DB::statement("UPDATE opt_out_records SET source = ? WHERE id = ?", [$request->input('source'), $record->id]);
        }

        $list->refreshCount();
        $record->load('optOutList');

        return response()->json(['data' => $record->toPortalArray()], 201);
    }

    public function optOutRecordsDestroy(string $id): JsonResponse
    {
        $record = OptOutRecord::findOrFail($id);
        $list = $record->optOutList;
        $record->delete();
        $list?->refreshCount();
        return response()->json(['success' => true, 'message' => 'Opt-out record removed']);
    }

    // =====================================================
    // TIMELINE
    // =====================================================

    public function timeline(Request $request, string $contactId): JsonResponse
    {
        $contact = Contact::findOrFail($contactId);

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
            // Cursor-based pagination: fetch events older than cursor event
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
            'total' => ContactTimelineEvent::forContact($contactId)->count(),
            'returned' => $pageEvents->count(),
            'cursor' => $nextCursor,
            'hasMore' => $hasMore,
        ]);
    }

    public function revealMsisdn(Request $request, string $contactId): JsonResponse
    {
        $contact = Contact::findOrFail($contactId);

        $request->validate(['reason' => 'nullable|string|max:500']);

        Log::info('MSISDN revealed', [
            'contact_id' => $contactId,
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

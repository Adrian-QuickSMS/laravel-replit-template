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
        $accountId = session('customer_tenant_id');
        $contact = DB::table('contacts')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->first();

        if (!$contact) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

        $tags = DB::table('tags')
            ->join('contact_tag', 'tags.id', '=', 'contact_tag.tag_id')
            ->where('contact_tag.contact_id', $id)
            ->select('tags.*')
            ->get();

        $lists = DB::table('contact_lists')
            ->join('contact_list_member', 'contact_lists.id', '=', 'contact_list_member.list_id')
            ->where('contact_list_member.contact_id', $id)
            ->select('contact_lists.*')
            ->get();

        $data = (array) $contact;
        $data['tags'] = $tags->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'color' => $t->color])->toArray();
        $data['lists'] = $lists->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->toArray();

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

        $validated['account_id'] = session('customer_tenant_id');
        $validated['created_by'] = session('customer_email', session('customer_user_id'));

        $contact = Contact::create($validated);
        $contact->load(['tags', 'lists']);

        return response()->json(['data' => $contact->toPortalArray()], 201);
    }

    public function contactsUpdate(Request $request, string $id): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $contactExists = DB::table('contacts')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$contactExists) {
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

        if (isset($validated['custom_data'])) {
            $validated['custom_data'] = json_encode($validated['custom_data']);
        }
        $validated['updated_by'] = session('customer_email', session('customer_user_id'));
        $validated['updated_at'] = now();

        DB::table('contacts')->where('id', $id)->update($validated);

        $contact = DB::table('contacts')->where('id', $id)->first();
        return response()->json(['data' => (array) $contact]);
    }

    public function contactsDestroy(string $id): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $contactExists = DB::table('contacts')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$contactExists) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

        DB::table('contact_tag')->where('contact_id', $id)->delete();
        DB::table('contact_list_member')->where('contact_id', $id)->delete();
        DB::table('contacts')->where('id', $id)->delete();
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
        $accountId = session('customer_tenant_id');
        $tagExists = DB::table('tags')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$tagExists) {
            return response()->json(['status' => 'error', 'message' => 'Tag not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        DB::table('tags')->where('id', $id)->update($validated);

        $tag = DB::table('tags')->where('id', $id)->first();
        return response()->json(['data' => [
            'id' => $tag->id,
            'name' => $tag->name,
            'color' => $tag->color,
        ]]);
    }

    public function tagsDestroy(string $id): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $tagExists = DB::table('tags')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$tagExists) {
            return response()->json(['status' => 'error', 'message' => 'Tag not found'], 404);
        }

        DB::table('contact_tag')->where('tag_id', $id)->delete();
        DB::table('tags')->where('id', $id)->delete();
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
        $accountId = session('customer_tenant_id');
        $listExists = DB::table('contact_lists')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$listExists) {
            return response()->json(['status' => 'error', 'message' => 'List not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|array',
        ]);

        if (isset($validated['rules'])) {
            $validated['rules'] = json_encode($validated['rules']);
        }

        DB::table('contact_lists')->where('id', $id)->update($validated);

        $list = DB::table('contact_lists')->where('id', $id)->first();
        return response()->json(['data' => [
            'id' => $list->id,
            'name' => $list->name,
            'description' => $list->description,
        ]]);
    }

    public function listsDestroy(string $id): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $listExists = DB::table('contact_lists')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$listExists) {
            return response()->json(['status' => 'error', 'message' => 'List not found'], 404);
        }

        DB::table('contact_list_member')->where('list_id', $id)->delete();
        DB::table('contact_lists')->where('id', $id)->delete();
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

        $deleted = DB::table('contact_list_member')
            ->where('list_id', $id)
            ->whereIn('contact_id', $request->input('contact_ids'))
            ->delete();

        DB::table('contact_lists')
            ->where('id', $id)
            ->update(['contact_count' => DB::table('contact_list_member')->where('list_id', $id)->count()]);

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
        $accountId = session('customer_tenant_id');
        $listExists = DB::table('opt_out_lists')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->exists();

        if (!$listExists) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::table('opt_out_lists')->where('id', $id)->update($validated);

        $list = DB::table('opt_out_lists')->where('id', $id)->first();
        return response()->json(['data' => [
            'id' => $list->id,
            'name' => $list->name,
            'description' => $list->description,
        ]]);
    }

    public function optOutListsDestroy(string $id): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $list = DB::table('opt_out_lists')
            ->where('id', $id)
            ->where('account_id', $accountId)
            ->first();

        if (!$list) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        if ($list->is_master) {
            return response()->json(['error' => 'Cannot delete the master opt-out list'], 422);
        }

        DB::table('opt_out_records')->where('opt_out_list_id', $id)->delete();
        DB::table('opt_out_lists')->where('id', $id)->delete();
        return response()->json(['success' => true, 'message' => 'Opt-out list deleted']);
    }

    public function optOutRecordsIndex(Request $request, string $listId): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $listExists = DB::table('opt_out_lists')
            ->where('id', $listId)
            ->where('account_id', $accountId)
            ->exists();

        if (!$listExists) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        $records = DB::table('opt_out_records')
            ->where('opt_out_list_id', $listId)
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 25));

        return response()->json([
            'data' => collect($records->items())->map(fn($r) => [
                'id' => $r->id,
                'mobile_number' => $r->mobile_number,
                'source' => $r->source ?? 'manual',
                'campaign_ref' => $r->campaign_ref,
                'opt_out_list_id' => $r->opt_out_list_id,
                'created_at' => $r->created_at,
            ]),
            'total' => $records->total(),
        ]);
    }

    public function optOutRecordsStore(Request $request, string $listId): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $listExists = DB::table('opt_out_lists')
            ->where('id', $listId)
            ->where('account_id', $accountId)
            ->exists();

        if (!$listExists) {
            return response()->json(['status' => 'error', 'message' => 'Opt-out list not found'], 404);
        }

        $validated = $request->validate([
            'mobile_number' => 'required|string|max:20',
            'campaign_ref' => 'nullable|string|max:255',
        ]);

        $recordId = (string) \Illuminate\Support\Str::uuid();
        DB::table('opt_out_records')->insert([
            'id' => $recordId,
            'mobile_number' => $validated['mobile_number'],
            'campaign_ref' => $validated['campaign_ref'] ?? null,
            'account_id' => $accountId,
            'opt_out_list_id' => $listId,
            'source' => $request->input('source', 'manual'),
            'created_at' => now(),
        ]);

        $newCount = DB::table('opt_out_records')->where('opt_out_list_id', $listId)->count();
        DB::table('opt_out_lists')->where('id', $listId)->update(['count' => $newCount]);

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
        $accountId = session('customer_tenant_id');
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
        $accountId = session('customer_tenant_id');
        $contactExists = DB::table('contacts')
            ->where('id', $contactId)
            ->where('account_id', $accountId)
            ->exists();

        if (!$contactExists) {
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
        $accountId = session('customer_tenant_id');
        $contact = DB::table('contacts')
            ->where('id', $contactId)
            ->where('account_id', $accountId)
            ->first();

        if (!$contact) {
            return response()->json(['status' => 'error', 'message' => 'Contact not found'], 404);
        }

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

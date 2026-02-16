<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SenderidRule;
use App\Models\ContentRule;
use App\Models\UrlRule;
use App\Models\NormalisationCharacter;
use App\Models\EnforcementExemption;
use App\Models\QuarantineMessage;
use App\Models\DomainAgeCache;
use App\Models\SystemSetting;
use App\Services\Admin\MessageEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EnforcementController extends Controller
{
    private MessageEnforcementService $enforcementService;

    public function __construct(MessageEnforcementService $enforcementService)
    {
        $this->enforcementService = $enforcementService;
    }

    private function adminEmail(): string
    {
        return session('admin_email', 'system');
    }

    public function senderidRulesIndex(): JsonResponse
    {
        $rules = SenderidRule::byPriority()->get();
        return response()->json(['success' => true, 'data' => $rules]);
    }

    public function senderidRulesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pattern' => 'required|string|max:255',
            'match_type' => 'required|string|in:exact,contains,regex,startswith,endswith',
            'action' => 'required|string|in:block,quarantine',
            'category' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:1',
            'use_normalisation' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = $this->adminEmail();
        $validated['use_normalisation'] = $validated['use_normalisation'] ?? true;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 100;

        $rule = SenderidRule::create($validated);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule], 201);
    }

    public function senderidRulesUpdate(Request $request, int $id): JsonResponse
    {
        $rule = SenderidRule::findOrFail($id);

        $validated = $request->validate([
            'pattern' => 'sometimes|string|max:255',
            'match_type' => 'sometimes|string|in:exact,contains,regex,startswith,endswith',
            'action' => 'sometimes|string|in:block,quarantine',
            'category' => 'nullable|string|max:100',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:1',
            'use_normalisation' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['updated_by'] = $this->adminEmail();
        $rule->update($validated);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule->fresh()]);
    }

    public function senderidRulesDestroy(int $id): JsonResponse
    {
        $rule = SenderidRule::findOrFail($id);
        $rule->delete();
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true]);
    }

    public function senderidRulesToggle(int $id): JsonResponse
    {
        $rule = SenderidRule::findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active, 'updated_by' => $this->adminEmail()]);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule->fresh()]);
    }

    public function contentRulesIndex(): JsonResponse
    {
        $rules = ContentRule::byPriority()->get();
        return response()->json(['success' => true, 'data' => $rules]);
    }

    public function contentRulesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pattern' => 'required|string|max:500',
            'match_type' => 'required|string|in:keyword,regex',
            'action' => 'required|string|in:block,quarantine',
            'category' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = $this->adminEmail();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 100;

        $rule = ContentRule::create($validated);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule], 201);
    }

    public function contentRulesUpdate(Request $request, int $id): JsonResponse
    {
        $rule = ContentRule::findOrFail($id);

        $validated = $request->validate([
            'pattern' => 'sometimes|string|max:500',
            'match_type' => 'sometimes|string|in:keyword,regex',
            'action' => 'sometimes|string|in:block,quarantine',
            'category' => 'nullable|string|max:100',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['updated_by'] = $this->adminEmail();
        $rule->update($validated);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule->fresh()]);
    }

    public function contentRulesDestroy(int $id): JsonResponse
    {
        $rule = ContentRule::findOrFail($id);
        $rule->delete();
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true]);
    }

    public function contentRulesToggle(int $id): JsonResponse
    {
        $rule = ContentRule::findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active, 'updated_by' => $this->adminEmail()]);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule->fresh()]);
    }

    public function urlRulesIndex(): JsonResponse
    {
        $rules = UrlRule::byPriority()->get();
        return response()->json(['success' => true, 'data' => $rules]);
    }

    public function urlRulesStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pattern' => 'required|string|max:500',
            'match_type' => 'required|string|in:exact_domain,wildcard,regex',
            'action' => 'required|string|in:block,quarantine',
            'category' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = $this->adminEmail();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 100;

        $rule = UrlRule::create($validated);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule], 201);
    }

    public function urlRulesUpdate(Request $request, int $id): JsonResponse
    {
        $rule = UrlRule::findOrFail($id);

        $validated = $request->validate([
            'pattern' => 'sometimes|string|max:500',
            'match_type' => 'sometimes|string|in:exact_domain,wildcard,regex',
            'action' => 'sometimes|string|in:block,quarantine',
            'category' => 'nullable|string|max:100',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['updated_by'] = $this->adminEmail();
        $rule->update($validated);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule->fresh()]);
    }

    public function urlRulesDestroy(int $id): JsonResponse
    {
        $rule = UrlRule::findOrFail($id);
        $rule->delete();
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true]);
    }

    public function urlRulesToggle(int $id): JsonResponse
    {
        $rule = UrlRule::findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active, 'updated_by' => $this->adminEmail()]);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $rule->fresh()]);
    }

    public function normalisationIndex(): JsonResponse
    {
        $characters = NormalisationCharacter::orderBy('base_character')->get();
        return response()->json(['success' => true, 'data' => $characters]);
    }

    public function normalisationUpdate(Request $request, int $id): JsonResponse
    {
        $char = NormalisationCharacter::findOrFail($id);

        $validated = $request->validate([
            'equivalents' => 'sometimes|array',
            'equivalents.*' => 'string',
            'is_active' => 'nullable|boolean',
        ]);

        $char->update($validated);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $char->fresh()]);
    }

    public function normalisationToggle(int $id): JsonResponse
    {
        $char = NormalisationCharacter::findOrFail($id);
        $char->update(['is_active' => !$char->is_active]);
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $char->fresh()]);
    }

    public function exemptionsIndex(Request $request): JsonResponse
    {
        $query = EnforcementExemption::query();

        if ($request->has('engine')) {
            $query->forEngine($request->input('engine'));
        }
        if ($request->has('scope')) {
            $query->forScope($request->input('scope'));
        }

        $exemptions = $query->orderBy('created_at', 'desc')->get();
        return response()->json(['success' => true, 'data' => $exemptions]);
    }

    public function exemptionsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'engine' => 'required|string|in:senderid,content,url',
            'exemption_type' => 'required|string|in:value,rule,account',
            'scope' => 'nullable|string|in:global,account',
            'value' => 'required|string|max:500',
            'rule_id' => 'nullable|string|max:255',
            'account_id' => 'nullable|uuid',
            'reason' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $validated['created_by'] = $this->adminEmail();
        $validated['scope'] = $validated['scope'] ?? 'global';
        $validated['is_active'] = $validated['is_active'] ?? true;

        $exemption = EnforcementExemption::create($validated);

        return response()->json(['success' => true, 'data' => $exemption], 201);
    }

    public function exemptionsUpdate(Request $request, int $id): JsonResponse
    {
        $exemption = EnforcementExemption::findOrFail($id);

        $validated = $request->validate([
            'engine' => 'sometimes|string|in:senderid,content,url',
            'exemption_type' => 'sometimes|string|in:value,rule,account',
            'scope' => 'nullable|string|in:global,account',
            'value' => 'sometimes|string|max:500',
            'rule_id' => 'nullable|string|max:255',
            'account_id' => 'nullable|uuid',
            'reason' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'expires_at' => 'nullable|date',
        ]);

        $exemption->update($validated);

        return response()->json(['success' => true, 'data' => $exemption->fresh()]);
    }

    public function exemptionsDestroy(int $id): JsonResponse
    {
        $exemption = EnforcementExemption::findOrFail($id);
        $exemption->delete();

        return response()->json(['success' => true]);
    }

    public function exemptionsToggle(int $id): JsonResponse
    {
        $exemption = EnforcementExemption::findOrFail($id);
        $exemption->update(['is_active' => !$exemption->is_active]);

        return response()->json(['success' => true, 'data' => $exemption->fresh()]);
    }

    public function quarantineIndex(Request $request): JsonResponse
    {
        $query = QuarantineMessage::with('recipients');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('engine')) {
            $query->forEngine($request->input('engine'));
        }

        $messages = $query->orderBy('created_at', 'desc')->limit(100)->get();
        return response()->json(['success' => true, 'data' => $messages]);
    }

    public function quarantineShow(int $id): JsonResponse
    {
        $message = QuarantineMessage::with('recipients')->findOrFail($id);
        return response()->json(['success' => true, 'data' => $message]);
    }

    public function quarantineRelease(Request $request, int $id): JsonResponse
    {
        $message = QuarantineMessage::findOrFail($id);

        if (!$message->isReviewable()) {
            return response()->json(['success' => false, 'error' => 'Message is not reviewable.'], 422);
        }

        $request->validate(['notes' => 'nullable|string']);

        $message->release($this->adminEmail(), $request->input('notes'));

        return response()->json(['success' => true, 'data' => $message->fresh()]);
    }

    public function quarantineBlock(Request $request, int $id): JsonResponse
    {
        $message = QuarantineMessage::findOrFail($id);

        if (!$message->isReviewable()) {
            return response()->json(['success' => false, 'error' => 'Message is not reviewable.'], 422);
        }

        $request->validate(['notes' => 'nullable|string']);

        $message->block($this->adminEmail(), $request->input('notes'));

        return response()->json(['success' => true, 'data' => $message->fresh()]);
    }

    public function settingsIndex(Request $request): JsonResponse
    {
        if ($request->has('group')) {
            $settings = SystemSetting::getGroup($request->input('group'));
        } else {
            $settings = SystemSetting::all();
        }

        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function settingsUpdate(Request $request, string $key): JsonResponse
    {
        $request->validate(['value' => 'required']);

        SystemSetting::setValue($key, $request->input('value'));

        return response()->json(['success' => true, 'data' => [
            'key' => $key,
            'value' => SystemSetting::getValue($key),
        ]]);
    }

    public function domainAgeCacheIndex(): JsonResponse
    {
        $cache = DomainAgeCache::orderBy('last_checked_at', 'desc')->limit(200)->get();
        return response()->json(['success' => true, 'data' => $cache]);
    }

    public function domainAgeCacheDestroy(int $id): JsonResponse
    {
        $entry = DomainAgeCache::findOrFail($id);
        $entry->delete();

        return response()->json(['success' => true]);
    }
}

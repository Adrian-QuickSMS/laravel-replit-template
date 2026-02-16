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
use Illuminate\Support\Facades\Log;

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

    private function validateRegexPattern(Request $request): ?JsonResponse
    {
        $matchType = $request->input('match_type');
        $pattern = $request->input('pattern');

        if (in_array($matchType, ['regex']) && $pattern) {
            if (!MessageEnforcementService::isValidRegex($pattern)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid or unsafe regex pattern. Avoid catastrophic backtracking constructs.',
                ], 422);
            }
        }

        return null;
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

        if ($regexError = $this->validateRegexPattern($request)) {
            return $regexError;
        }

        $validated['created_by'] = $this->adminEmail();
        $validated['use_normalisation'] = $validated['use_normalisation'] ?? true;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 100;

        try {
            $rule = SenderidRule::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('SenderID rule creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to create rule. Check constraint violation.'], 422);
        }

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

        if ($regexError = $this->validateRegexPattern($request)) {
            return $regexError;
        }

        $validated['updated_by'] = $this->adminEmail();

        try {
            $rule->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('SenderID rule update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to update rule. Check constraint violation.'], 422);
        }

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

        if ($regexError = $this->validateRegexPattern($request)) {
            return $regexError;
        }

        $validated['created_by'] = $this->adminEmail();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 100;

        try {
            $rule = ContentRule::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Content rule creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to create rule. Check constraint violation.'], 422);
        }

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

        if ($regexError = $this->validateRegexPattern($request)) {
            return $regexError;
        }

        $validated['updated_by'] = $this->adminEmail();

        try {
            $rule->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Content rule update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to update rule. Check constraint violation.'], 422);
        }

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

        if ($regexError = $this->validateRegexPattern($request)) {
            return $regexError;
        }

        $validated['created_by'] = $this->adminEmail();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['priority'] = $validated['priority'] ?? 100;

        try {
            $rule = UrlRule::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('URL rule creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to create rule. Check constraint violation.'], 422);
        }

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

        if ($regexError = $this->validateRegexPattern($request)) {
            return $regexError;
        }

        $validated['updated_by'] = $this->adminEmail();

        try {
            $rule->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('URL rule update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to update rule. Check constraint violation.'], 422);
        }

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
            'exemption_type' => 'required|string|in:value,rule,engine',
            'scope' => 'nullable|string|in:global,account,sub_account',
            'value' => 'required|string|max:500',
            'rule_id' => 'nullable|integer',
            'account_id' => 'nullable|uuid',
            'reason' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = $this->adminEmail();
        $validated['scope'] = $validated['scope'] ?? 'global';
        $validated['is_active'] = $validated['is_active'] ?? true;

        try {
            $exemption = EnforcementExemption::create($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Exemption creation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to create exemption. Check constraint violation.'], 422);
        }

        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $exemption], 201);
    }

    public function exemptionsUpdate(Request $request, int $id): JsonResponse
    {
        $exemption = EnforcementExemption::findOrFail($id);

        $validated = $request->validate([
            'engine' => 'sometimes|string|in:senderid,content,url',
            'exemption_type' => 'sometimes|string|in:value,rule,engine',
            'scope' => 'nullable|string|in:global,account,sub_account',
            'value' => 'sometimes|string|max:500',
            'rule_id' => 'nullable|integer',
            'account_id' => 'nullable|uuid',
            'reason' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $exemption->update($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Exemption update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'error' => 'Failed to update exemption. Check constraint violation.'], 422);
        }

        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true, 'data' => $exemption->fresh()]);
    }

    public function exemptionsDestroy(int $id): JsonResponse
    {
        $exemption = EnforcementExemption::findOrFail($id);
        $exemption->delete();
        $this->enforcementService->hotReloadRules();

        return response()->json(['success' => true]);
    }

    public function exemptionsToggle(int $id): JsonResponse
    {
        $exemption = EnforcementExemption::findOrFail($id);
        $exemption->update(['is_active' => !$exemption->is_active]);
        $this->enforcementService->hotReloadRules();

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

        $request->validate(['notes' => 'nullable|string']);

        try {
            if (!$message->isReviewable()) {
                return response()->json(['success' => false, 'error' => 'Message is not in a reviewable state.'], 422);
            }

            $message->release($this->adminEmail(), $request->input('notes', ''));
        } catch (\LogicException $e) {
            Log::warning('Quarantine release failed', ['id' => $id]);
            return response()->json(['success' => false, 'error' => 'Unable to release message. It may have already been processed.'], 422);
        }

        return response()->json(['success' => true, 'data' => $message->fresh()]);
    }

    public function quarantineBlock(Request $request, int $id): JsonResponse
    {
        $message = QuarantineMessage::findOrFail($id);

        $request->validate(['notes' => 'nullable|string']);

        try {
            if (!$message->isReviewable()) {
                return response()->json(['success' => false, 'error' => 'Message is not in a reviewable state.'], 422);
            }

            $message->block($this->adminEmail(), $request->input('notes', ''));
        } catch (\LogicException $e) {
            Log::warning('Quarantine block failed', ['id' => $id]);
            return response()->json(['success' => false, 'error' => 'Unable to block message. It may have already been processed.'], 422);
        }

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

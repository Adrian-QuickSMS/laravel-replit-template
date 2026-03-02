# Replit Prompt: Campaign Creation Security & Performance Hardening

## Scope — Read This First

This prompt fixes **security and performance issues in the campaign creation pipeline only**. The scope is tight:

**IN SCOPE — These files, these issues:**
- `app/Http/Controllers/Api/CampaignApiController.php` — authorization gaps, input validation
- `app/Http/Controllers/QuickSMSController.php` — session-based confirm flow issues (only `confirmAndSend`, `storeCampaignConfig`, `confirmCampaign` methods)
- `app/Services/Campaign/CampaignService.php` — race conditions, missing transaction wrapping
- `app/Services/Campaign/BillingPreflightService.php` — double pricing lookup
- `resources/views/quicksms/messages/confirm-campaign.blade.php` — hardcoded pricing, missing real cost display

**OUT OF SCOPE — Do NOT touch these:**
- `RecipientResolverService.php` — already well-structured with chunked streaming
- `DeliveryService.php` — delivery pipeline is separate
- `OptOutService.php` — opt-out system is separate
- `RcsContentValidator.php` / `RcsAssetService.php` — RCS pipeline is separate
- `ProcessCampaignBatch.php` / `ScheduledCampaignDispatcher.php` — queue jobs are separate
- `send-message.blade.php` — the Send Message form UI is separate
- Any migration files — do not create or modify migrations
- Any model files — do not modify Campaign, CampaignRecipient, or MessageTemplate models
- Auth system, admin system, supplier system — completely unrelated

---

## Pull the branch first

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git checkout claude/quicksms-security-performance-dr8sw
php artisan route:clear && php artisan config:clear
```

---

## Issue 1: CampaignApiController — Missing Ownership Verification on Route-Model Binding

**File:** `app/Http/Controllers/Api/CampaignApiController.php`

**Problem:** Every method that takes a campaign `{id}` parameter uses `Campaign::find($id)`. The Campaign model has a global scope that filters by `account_id`, which provides tenant isolation. However, if the global scope is ever bypassed, removed, or misconfigured, all campaigns become visible. There should be an explicit ownership check as defence-in-depth.

**Fix:** Add a private helper method that wraps `find()` with an explicit tenant check, and use it everywhere.

### 1a. Add `findCampaignOrFail` helper

Add this private method to the controller (after the existing `tenantId()` method):

```php
/**
 * Find a campaign by ID with explicit tenant ownership check.
 * Defence-in-depth: does not rely solely on global scope for isolation.
 */
private function findCampaignOrFail(string $id): ?Campaign
{
    $campaign = Campaign::find($id);

    if (!$campaign) {
        return null;
    }

    // Explicit tenant ownership check — defence-in-depth
    if ($campaign->account_id !== $this->tenantId()) {
        Log::warning('[CampaignApi] Tenant mismatch on campaign access', [
            'campaign_id' => $id,
            'campaign_account' => $campaign->account_id,
            'request_tenant' => $this->tenantId(),
        ]);
        return null; // Return null (same as not found) to avoid leaking existence
    }

    return $campaign;
}
```

### 1b. Replace all `Campaign::find($id)` calls

In every method that takes `string $id` as a parameter, replace:
```php
$campaign = Campaign::find($id);
```
with:
```php
$campaign = $this->findCampaignOrFail($id);
```

**Methods to update (12 total):**
`show`, `update`, `destroy`, `applyTemplate`, `previewRecipients`, `resolveRecipients`, `recipients`, `prepare`, `preparationStatus`, `estimateCost`, `validate_`, `sendNow`, `schedule`, `pause`, `resume`, `cancel`, `clone`

The null check after `find()` already exists in each method — no other changes needed.

---

## Issue 2: CampaignApiController — `sender_id_id` and `rcs_agent_id` Accept Integers, Should Be UUIDs

**File:** `app/Http/Controllers/Api/CampaignApiController.php`

**Problem:** The `store()` and `update()` validation rules have:
```php
'sender_id_id' => 'nullable|integer',
'rcs_agent_id' => 'nullable|integer',
```

The `sender_ids` and `rcs_agents` tables use UUID primary keys (PostgreSQL native UUIDs). Passing an integer will silently fail or cause a PostgreSQL type cast error. The validation should require UUID format.

**Fix:** In both `store()` and `update()` methods, change:
```php
'sender_id_id' => 'nullable|integer',
'rcs_agent_id' => 'nullable|integer',
```
to:
```php
'sender_id_id' => 'nullable|uuid',
'rcs_agent_id' => 'nullable|uuid',
```

**Locations:**
- `store()` method — around line 110-111
- `update()` method — around line 184-185

---

## Issue 3: CampaignApiController — `recipient_sources.*.data` Allows 1 Million Items Without Server-Side Limit

**File:** `app/Http/Controllers/Api/CampaignApiController.php`

**Problem:** The `store()` method validates:
```php
'recipient_sources.*.data' => 'nullable|array|max:1000000',
```

A single API request with 1,000,000 CSV data items would consume ~500MB+ of memory during validation and processing. This is a denial-of-service vector.

**Fix:** Reduce the limit to a reasonable maximum (100,000 rows per source, which is generous). In `store()`:

```php
'recipient_sources.*.data' => 'nullable|array|max:100000',
```

Also add the same validation to `update()` if `recipient_sources.*.data` is accepted there (currently it is not — which is correct, keep it that way).

---

## Issue 4: CampaignService::create() and sendNow() — Missing Transaction Wrapping

**File:** `app/Services/Campaign/CampaignService.php`

**Problem:** `create()` does Campaign::create() followed by a conditional `$campaign->save()` for encoding recalculation. If the save fails, a campaign record exists without correct encoding/segments. More critically, `sendNow()` does validate → billing preflight → content resolve → transition to queued. If the content resolution fails after funds are reserved, the reservation is orphaned.

**Fix:** Wrap `sendNow()` in a database transaction so that fund reservation and campaign status transition are atomic. If anything fails, the reservation is rolled back.

### 4a. Wrap `sendNow()` in a transaction

Replace the `sendNow()` method body:

```php
public function sendNow(Campaign $campaign): PreflightResult
{
    if (!$campaign->isDraft()) {
        throw new \RuntimeException("Campaign must be in draft status to send. Current: {$campaign->status}");
    }

    // Validate before entering the transaction
    $errors = $this->validateForSend($campaign);
    if (!empty($errors)) {
        throw ValidationException::withMessages(['campaign' => $errors]);
    }

    return DB::transaction(function () use ($campaign) {
        // Billing preflight (estimate, balance check, reserve funds)
        $preflightResult = $this->billingPreflight->runPreflight($campaign);

        // Resolve per-recipient content (merge fields)
        $this->resolveRecipientContent($campaign);

        // Transition to queued (ready for queue workers to pick up)
        $campaign->transitionTo(Campaign::STATUS_QUEUED);

        Log::info('[CampaignService] Campaign queued for immediate send', [
            'campaign_id' => $campaign->id,
            'estimated_cost' => $preflightResult->estimatedCost,
            'reservation_id' => $preflightResult->reservationId,
        ]);

        return $preflightResult;
    });
}
```

### 4b. Wrap `processScheduled()` in a transaction (same pattern)

```php
public function processScheduled(Campaign $campaign): PreflightResult
{
    if (!$campaign->isScheduled()) {
        throw new \RuntimeException("Campaign is not in scheduled status.");
    }

    return DB::transaction(function () use ($campaign) {
        // Force re-resolution — contact data may have changed since scheduling
        $campaign->update(['content_resolved_at' => null]);

        // Billing preflight
        $preflightResult = $this->billingPreflight->runPreflight($campaign);

        // Resolve per-recipient content
        $this->resolveRecipientContent($campaign);

        // Transition to queued
        $campaign->transitionTo(Campaign::STATUS_QUEUED);

        Log::info('[CampaignService] Scheduled campaign queued for send', [
            'campaign_id' => $campaign->id,
        ]);

        return $preflightResult;
    });
}
```

---

## Issue 5: CampaignService::prepareCampaign() — Race Condition on Double-Submit

**File:** `app/Services/Campaign/CampaignService.php`

**Problem:** If a user clicks "Continue" twice quickly on the Send Message page, two `POST /api/campaigns/{id}/prepare` requests arrive. Both check `isDraft()` → true, both delete existing recipients, both dispatch `ResolveRecipientContentJob`. This creates duplicate recipient rows and double content resolution jobs.

**Fix:** Use a DB-level advisory lock (or a simple `preparation_status` guard) to prevent concurrent preparation.

In `prepareCampaign()`, add a guard at the top (after the draft check):

```php
public function prepareCampaign(Campaign $campaign): ResolverResult
{
    if (!$campaign->isDraft()) {
        throw new \RuntimeException("Campaign must be in draft status to prepare.");
    }

    // Prevent concurrent preparation: atomically check-and-set preparation_status
    $updated = DB::table('campaigns')
        ->where('id', $campaign->id)
        ->whereIn('preparation_status', [null, 'ready', 'failed'])
        ->update([
            'preparation_status' => 'preparing',
            'preparation_progress' => 0,
            'preparation_error' => null,
            'content_resolved_at' => null,
        ]);

    if ($updated === 0) {
        // Another request is already preparing this campaign
        throw new \RuntimeException("Campaign is already being prepared.");
    }

    // Clear any existing recipients (supports re-preparation after message edits)
    DB::table('campaign_recipients')
        ->where('campaign_id', $campaign->id)
        ->delete();

    // Remove the old manual update since we did it atomically above
    // (delete the $campaign->update([...]) block that was here)

    // Step 1: Resolve recipients synchronously
    $resolverResult = $this->recipientResolver->resolve($campaign);

    if ($resolverResult->totalCreated === 0) {
        $campaign->update([
            'preparation_status' => 'ready',
            'preparation_progress' => 100,
            'content_resolved_at' => now(),
        ]);
        return $resolverResult;
    }

    // Step 2: Dispatch async content resolution
    ResolveRecipientContentJob::dispatch($campaign->id);

    Log::info('[CampaignService] Campaign preparation started', [
        'campaign_id' => $campaign->id,
        'recipients_resolved' => $resolverResult->totalCreated,
    ]);

    return $resolverResult;
}
```

The key change is the atomic `UPDATE ... WHERE preparation_status IN (null, 'ready', 'failed')`. If two requests race, only one gets `$updated === 1`. The other gets `0` and throws immediately.

---

## Issue 6: BillingPreflightService::createEstimateSnapshot() — Redundant Pricing Lookup

**File:** `app/Services/Campaign/BillingPreflightService.php`

**Problem:** `runPreflight()` calls `estimateCost()` or `estimateCostPerSegmentGroup()`, which resolves pricing for every country via `PricingEngine::calculateMessageCost()`. Then `createEstimateSnapshot()` calls `buildPricingSnapshot()`, which calls `PricingEngine::resolvePrice()` again for every country. This duplicates the pricing lookup — 2x the database queries for no reason.

**Fix:** Pass the already-resolved pricing data from the estimate into the snapshot instead of looking it up again. Modify `buildPricingSnapshot()` to extract pricing from the CostEstimate's `perCountryCosts` (which already contains `unit_price`, `currency`, and `price_source`).

Replace the `buildPricingSnapshot()` method:

```php
/**
 * Build a snapshot from the already-resolved pricing in the cost estimate.
 * Avoids redundant PricingEngine lookups.
 */
private function buildPricingSnapshot(Account $account, string $billableProductType, array $perCountryCosts): array
{
    $snapshot = [];

    foreach ($perCountryCosts as $countryKey => $data) {
        $snapshot[] = [
            'product_type' => $billableProductType,
            'country_iso' => $data['country_iso'] ?? null,
            'unit_price' => $data['unit_price'] ?? null,
            'currency' => $data['currency'] ?? ($account->currency ?? 'GBP'),
            'source' => $data['price_source'] ?? 'estimate',
            'price_id' => null, // Not available from estimate; acceptable for audit snapshot
        ];
    }

    return $snapshot;
}
```

> **Note:** If you need the `price_id` in the snapshot for full audit traceability, you can add it to the CostEstimate data in the `estimateCost()` method instead. But for now, the unit_price + source is sufficient — the `price_id` can be looked up from `customer_prices` or `product_tier_prices` by (product_type, country_iso, unit_price) if needed during a dispute.

---

## Issue 7: QuickSMSController::confirmAndSend() — Leaks Exception Messages to Client

**File:** `app/Http/Controllers/QuickSMSController.php`

**Problem:** The catch block at line ~608 returns the raw exception message to the API response:
```php
'message' => 'Failed to send campaign: ' . $e->getMessage(),
```

This can leak internal details (table names, SQL errors, class names) to the end user.

**Fix:** Return a generic message and log the details server-side only. Replace the generic catch block (around line 608):

```php
} catch (\Exception $e) {
    \Log::error('Campaign confirmAndSend failed', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'account_id' => $accountId,
        'session_data_keys' => array_keys($sessionData),
    ]);

    return response()->json([
        'success' => false,
        'message' => 'An error occurred while sending your campaign. Please try again or contact support.',
    ], 500);
}
```

---

## Issue 8: QuickSMSController::confirmCampaign() — Hardcoded Pricing on Confirm Page

**File:** `app/Http/Controllers/QuickSMSController.php` (method `confirmCampaign`, around line 491)

**Problem:** The confirm page displays hardcoded pricing:
```php
$pricing = [
    'sms_unit_price' => 0.023,
    'rcs_basic_price' => 0.035,
    'rcs_single_price' => 0.045,
    'vat_applicable' => true,
    'vat_rate' => 20,
];
```

This shows fake prices regardless of the customer's actual pricing tier. The `dashboard()` method in the same controller already resolves real pricing from `CustomerPrice` and `ProductTierPrice` — this pattern should be reused here.

**Fix:** Replace the hardcoded `$pricing` block in `confirmCampaign()` with real pricing lookup. Copy the pattern from `dashboard()`:

```php
// Pricing data — resolve from account's actual pricing
$accountId = session('customer_tenant_id');
$account = \App\Models\Account::withoutGlobalScopes()->find($accountId);

$pricing = [
    'sms_unit_price' => 0.023,   // Fallback defaults
    'rcs_basic_price' => 0.035,
    'rcs_single_price' => 0.045,
    'vat_applicable' => (bool) ($account->vat_registered ?? true),
    'vat_rate' => ($account->vat_registered ?? true) ? 20 : 0,
];

if ($accountId && $account) {
    $productTypes = ['sms', 'rcs_basic', 'rcs_single'];
    $priceMap = ['sms' => 'sms_unit_price', 'rcs_basic' => 'rcs_basic_price', 'rcs_single' => 'rcs_single_price'];

    // Check customer-specific prices first
    $customerPrices = \App\Models\Billing\CustomerPrice::where('account_id', $accountId)
        ->whereIn('product_type', $productTypes)
        ->whereNull('country_iso')
        ->active()
        ->validAt()
        ->get()
        ->keyBy('product_type');

    foreach ($productTypes as $type) {
        if ($customerPrices->has($type)) {
            $pricing[$priceMap[$type]] = (float) $customerPrices[$type]->unit_price;
        }
    }

    // Fall back to tier pricing for any not found
    $missingTypes = array_filter($productTypes, fn($t) => !$customerPrices->has($t));
    if (!empty($missingTypes)) {
        $tier = $account->product_tier ?? 'starter';
        $tierPrices = \App\Models\Billing\ProductTierPrice::where('product_tier', $tier)
            ->whereIn('product_type', $missingTypes)
            ->whereNull('country_iso')
            ->active()
            ->validAt()
            ->get()
            ->keyBy('product_type');

        foreach ($missingTypes as $type) {
            if ($tierPrices->has($type)) {
                $pricing[$priceMap[$type]] = (float) $tierPrices[$type]->unit_price;
            }
        }
    }
}
```

---

## Issue 9: QuickSMSController::storeCampaignConfig() — No Input Validation

**File:** `app/Http/Controllers/QuickSMSController.php` (method `storeCampaignConfig`, line 516)

**Problem:** `storeCampaignConfig()` blindly stores any request data matching the allowed keys into the session. There is no validation on data types or sizes. An attacker could send a 100MB `recipient_sources` array or inject unexpected data types.

**Fix:** Add basic validation before storing:

```php
public function storeCampaignConfig(Request $request)
{
    $validated = $request->validate([
        'campaign_name' => 'nullable|string|max:255',
        'channel' => 'nullable|string|in:sms_only,basic_rcs,rich_rcs',
        'sender_id' => 'nullable|string|max:50',
        'sender_id_id' => 'nullable',
        'rcs_agent' => 'nullable|string|max:100',
        'rcs_agent_id' => 'nullable',
        'campaign_type' => 'nullable|string|in:sms,rcs_basic,rcs_single,rcs_carousel',
        'message_content' => 'nullable|string|max:10000',
        'rcs_content' => 'nullable|array',
        'recipient_sources' => 'nullable|array|max:50',
        'scheduled_time' => 'nullable|string|max:50',
        'message_expiry' => 'nullable|string|max:10',
        'sending_window' => 'nullable|string|max:50',
        'recipient_count' => 'nullable|integer|min:0|max:10000000',
        'valid_count' => 'nullable|integer|min:0|max:10000000',
        'invalid_count' => 'nullable|integer|min:0|max:10000000',
        'opted_out_count' => 'nullable|integer|min:0|max:10000000',
        'sources' => 'nullable|array',
        'optout_config' => 'nullable|array',
    ]);

    $request->session()->put('campaign_config', $validated);

    return response()->json(['success' => true]);
}
```

---

## Issue 10: CampaignApiController::sendNow() — Missing Batch Job Dispatch Error Handling

**File:** `app/Http/Controllers/Api/CampaignApiController.php`

**Problem:** `sendNow()` calls `$this->dispatchBatchJobs($campaign)` after the campaign is already in `queued` status. If the batch job dispatch fails (e.g., Redis/queue connection down), the campaign is stuck in `queued` with no jobs to process it. The user sees "Campaign queued for sending" but nothing ever sends.

**Fix:** Wrap the batch dispatch in a try-catch and fail the campaign if dispatch fails:

```php
public function sendNow(string $id): JsonResponse
{
    $campaign = $this->findCampaignOrFail($id);

    if (!$campaign) {
        return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
    }

    try {
        $result = $this->campaignService->sendNow($campaign);

        // Dispatch batch jobs — if this fails, mark campaign as failed
        try {
            $this->dispatchBatchJobs($campaign);
        } catch (\Exception $e) {
            Log::error('[CampaignApi] Failed to dispatch batch jobs', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);
            $this->campaignService->fail($campaign, 'Failed to dispatch delivery jobs: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign was created but could not be queued for delivery. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Campaign queued for sending',
            'data' => $result->toArray(),
        ]);
    } catch (ValidationException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Campaign validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\RuntimeException $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
    }
}
```

Apply the same pattern to `resume()`:
```php
public function resume(string $id): JsonResponse
{
    $campaign = $this->findCampaignOrFail($id);

    if (!$campaign) {
        return response()->json(['status' => 'error', 'message' => 'Campaign not found'], 404);
    }

    try {
        $this->campaignService->resume($campaign);

        try {
            $this->dispatchBatchJobs($campaign);
        } catch (\Exception $e) {
            Log::error('[CampaignApi] Failed to dispatch batch jobs on resume', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the campaign on resume — it can be retried
            return response()->json([
                'status' => 'error',
                'message' => 'Campaign resumed but batch dispatch failed. Please try resuming again.',
            ], 500);
        }

        return response()->json(['success' => true, 'message' => 'Campaign resumed']);
    } catch (\InvalidArgumentException $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
    }
}
```

---

## Issue 11: confirm-campaign.blade.php — Pricing Calculated Client-Side from Blade Variables

**File:** `resources/views/quicksms/messages/confirm-campaign.blade.php`

**Problem:** Lines 178-206 calculate the total cost client-side using Blade PHP inline:
```php
@php
    $messageCount = $recipients['valid'];
    $subtotal = $messageCount * $pricing['sms_unit_price'];
    $vatAmount = $pricing['vat_applicable'] ? $subtotal * ($pricing['vat_rate'] / 100) : 0;
    $total = $subtotal + $vatAmount;
@endphp
```

This multiplies a front-end-supplied `valid` count (from session) by the unit price. If the session `valid_count` is manipulated (e.g., by tampering with the `storeCampaignConfig` POST), the displayed price is wrong. The backend `BillingPreflightService` does the authoritative calculation — the confirm page should show the backend's estimate.

**Fix:** This requires a two-part change:

### 11a. Controller: Pass the real cost estimate to the view

In `confirmCampaign()`, after resolving pricing, add a call to get the real estimate if a campaign ID exists in the session:

```php
// After the $pricing block, add:
$realEstimate = null;
if (!empty($sessionData['campaign_id'])) {
    $campaign_record = \App\Models\Campaign::find($sessionData['campaign_id']);
    if ($campaign_record && $campaign_record->preparation_status === 'ready') {
        try {
            $campaignService = app(\App\Services\Campaign\CampaignService::class);
            $costEstimate = $campaignService->estimateCost($campaign_record);
            $realEstimate = $costEstimate->toArray();
        } catch (\Exception $e) {
            // Fall back to session-based estimate
        }
    }
}
```

Pass `$realEstimate` to the view:
```php
return view('quicksms.messages.confirm-campaign', [
    'page_title' => 'Confirm & Send Campaign',
    'campaign' => $campaign,
    'channel' => $channel,
    'recipients' => $recipients,
    'pricing' => $pricing,
    'message' => $message,
    'realEstimate' => $realEstimate,  // ADD THIS
]);
```

### 11b. Blade: Display real estimate when available

In `confirm-campaign.blade.php`, update the SMS pricing section (lines ~177-206). Before the existing `@php` block, add a check for the real estimate:

```blade
@if($channel['type'] === 'sms_only')
    @if(!empty($realEstimate))
        {{-- Real estimate from backend billing engine --}}
        <div class="row mb-2">
            <div class="col-6 text-muted">Messages</div>
            <div class="col-6 text-end">{{ number_format($recipients['valid']) }}</div>
        </div>
        <div class="row mb-2">
            <div class="col-6 text-muted">Estimated Cost (ex VAT)</div>
            <div class="col-6 text-end">&pound;{{ number_format((float) $realEstimate['total_cost'], 2) }}</div>
        </div>
        @if(!$realEstimate['has_sufficient_balance'])
        <div class="alert alert-warning py-2 px-3 mt-2" style="font-size: 13px;">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Insufficient balance. Available: &pound;{{ number_format((float) $realEstimate['available_balance'], 2) }}
        </div>
        @endif
        <hr>
        <div class="row">
            <div class="col-6 fw-bold">Estimated Total</div>
            <div class="col-6 text-end fw-bold h5 mb-0">&pound;{{ number_format((float) $realEstimate['total_cost'], 2) }}</div>
        </div>
    @else
        {{-- Fallback: simple estimate from session data --}}
        @php
            $messageCount = $recipients['valid'];
            $subtotal = $messageCount * $pricing['sms_unit_price'];
            $vatAmount = $pricing['vat_applicable'] ? $subtotal * ($pricing['vat_rate'] / 100) : 0;
            $total = $subtotal + $vatAmount;
        @endphp
        {{-- ... keep existing display code as fallback ... --}}
    @endif
@else
    {{-- ... keep existing RCS pricing notice ... --}}
@endif
```

---

## Summary of All Changes

| # | File | What | Why |
|---|------|------|-----|
| 1 | `CampaignApiController.php` | Add `findCampaignOrFail()` with explicit tenant check | Defence-in-depth tenant isolation |
| 2 | `CampaignApiController.php` | Change `sender_id_id` and `rcs_agent_id` validation to `uuid` | Correct type validation for PostgreSQL UUIDs |
| 3 | `CampaignApiController.php` | Reduce `recipient_sources.*.data` max from 1M to 100K | Prevent memory exhaustion DoS |
| 4 | `CampaignService.php` | Wrap `sendNow()` and `processScheduled()` in `DB::transaction()` | Atomic fund reservation + status transition |
| 5 | `CampaignService.php` | Add atomic `preparation_status` guard in `prepareCampaign()` | Prevent race condition on double-submit |
| 6 | `BillingPreflightService.php` | Reuse pricing data in `buildPricingSnapshot()` | Eliminate redundant pricing DB queries |
| 7 | `QuickSMSController.php` | Remove raw exception message from `confirmAndSend()` response | Prevent information leakage |
| 8 | `QuickSMSController.php` | Replace hardcoded pricing with real pricing lookup in `confirmCampaign()` | Show real prices on confirm page |
| 9 | `QuickSMSController.php` | Add validation rules to `storeCampaignConfig()` | Prevent oversized/malformed session data |
| 10 | `CampaignApiController.php` | Add error handling around `dispatchBatchJobs()` in `sendNow()` and `resume()` | Handle queue dispatch failure gracefully |
| 11 | `confirm-campaign.blade.php` + `QuickSMSController.php` | Display real backend cost estimate when available | Accurate pricing on confirm page |

---

## What NOT to Change

- **Do not modify** any model files (`Campaign.php`, `CampaignRecipient.php`, `MessageTemplate.php`)
- **Do not modify** `RecipientResolverService.php` — the chunked streaming pipeline is working correctly
- **Do not modify** `DeliveryService.php` or any queue job files
- **Do not modify** `send-message.blade.php` — the Send Message form is not in scope
- **Do not modify** `OptOutService.php` or any opt-out related code
- **Do not modify** `RcsContentValidator.php` or `RcsAssetService.php`
- **Do not create** any new migration files
- **Do not create** any new model files
- **Do not modify** routes — all existing routes remain unchanged
- **Do not add** new npm packages or Composer dependencies

---

## Testing Checklist

### Security
- [ ] Access a campaign belonging to another account via API → returns 404 (not 403, to avoid leaking existence)
- [ ] Submit `sender_id_id` as an integer → validation error (must be UUID)
- [ ] Submit `recipient_sources.*.data` with 200,000 items → validation error (max 100,000)
- [ ] Call `POST /messages/confirm-send` with tampered session data → generic error message (no SQL or class names leaked)
- [ ] Call `POST /messages/store-campaign-config` with oversized `message_content` (>10,000 chars) → validation error

### Race Conditions
- [ ] Click "Continue" twice rapidly on Send Message → second request returns "Campaign is already being prepared"
- [ ] Verify `preparation_status` transitions: null → preparing → ready (never two preparing states)

### Performance
- [ ] Run billing preflight → confirm only one round of pricing lookups occurs (check logs)
- [ ] Confirm page loads with real pricing (not hardcoded £0.023)

### Transaction Safety
- [ ] If content resolution fails mid-way through `sendNow()`, verify fund reservation is rolled back
- [ ] If queue dispatch fails after `sendNow()`, campaign is marked as failed (not stuck in queued)

### Confirm Page
- [ ] Confirm page shows real cost from backend when campaign has been prepared
- [ ] Confirm page shows fallback estimate when campaign has not been prepared
- [ ] Insufficient balance warning appears when balance is too low

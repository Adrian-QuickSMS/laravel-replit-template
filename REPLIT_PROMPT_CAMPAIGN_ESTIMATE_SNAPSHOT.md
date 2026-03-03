# Replit Prompt: Merge CampaignEstimateSnapshot — Immutable Pricing Record at Send Time

## What This Is

An immutable pricing snapshot that gets frozen at the moment a campaign is sent. It captures the exact cost estimate, VAT calculation, per-country breakdown, and pricing rates that were shown to the customer. Even if prices change later, this record proves what was estimated and reserved.

Required for: invoice dispute resolution, NHS/enterprise audit trail, ISO27001 evidence, HMRC 7-year financial records.

## Scope — CRITICAL: Read Before Starting

This change is **strictly limited to campaign pricing estimates**. It adds 2 new files and makes small, targeted additions to 4 existing files. That's it.

**DO NOT touch any of these:**
- `CampaignApiController.php` — no changes needed
- `QuickSMSController.php` — no changes needed
- `CampaignService.php` — no changes needed
- `RecipientResolverService.php` — no changes needed
- `OptOutService.php` — no changes needed
- `confirm-campaign.blade.php` — no changes needed
- `send-message.blade.php` — no changes needed
- Any route files — no changes needed
- Any other controller, service, view, or middleware

---

## Step 1: Create the Migration

Create **new file** `database/migrations/2026_03_02_000001_create_campaign_estimate_snapshots_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CampaignEstimateSnapshot — immutable record of the pricing estimate
     * at the moment a campaign transitions to sending.
     *
     * This table captures exactly what the portal showed the customer and what
     * balance was reserved, even if prices, penetration rates, or tariffs change
     * later. Required for:
     *   - Invoice dispute resolution
     *   - NHS / enterprise audit compliance
     *   - ISO27001 evidence trail
     *   - HMRC financial record keeping (7-year retention)
     */
    public function up(): void
    {
        Schema::create('campaign_estimate_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('campaign_id')->unique();
            $table->uuid('account_id');

            // ── What was estimated ──────────────────────────────────
            $table->string('product_type', 30);            // sms, rcs_basic, rcs_single (carousel maps to rcs_single)
            $table->string('campaign_type', 30);            // original campaign type (may differ from product_type for carousel)
            $table->string('currency', 3)->default('GBP');

            // ── Totals ─────────────────────────────────────────────
            $table->integer('total_recipients');
            $table->decimal('estimated_cost', 14, 4);       // net cost before VAT
            $table->decimal('vat_rate', 5, 2)->default(0);  // e.g. 20.00
            $table->decimal('vat_amount', 14, 4)->default(0);
            $table->decimal('estimated_cost_inc_vat', 14, 4); // gross cost
            $table->decimal('reserved_amount', 14, 4);      // amount locked in reservation

            // ── Balance at send time ───────────────────────────────
            $table->decimal('available_balance_at_send', 14, 4);
            $table->boolean('is_postpay')->default(false);
            $table->string('product_tier', 20)->nullable();  // starter, enterprise, bespoke

            // ── Per-country pricing breakdown (JSONB) ──────────────
            $table->jsonb('country_breakdown');

            // ── Pricing snapshot ────────────────────────────────────
            $table->jsonb('pricing_snapshot');

            // ── Errors / warnings at estimation time ───────────────
            $table->jsonb('estimation_errors')->nullable();

            // ── RCS-specific fields ────────────────────────────────
            $table->decimal('rcs_penetration_rate', 5, 2)->nullable();
            $table->integer('expected_rcs_count')->nullable();
            $table->integer('expected_sms_fallback_count')->nullable();

            // ── Metadata ───────────────────────────────────────────
            $table->uuid('reservation_id')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('snapshot_at');
            $table->timestamps();

            // ── Indexes ────────────────────────────────────────────
            $table->index('account_id');
            $table->index('snapshot_at');

            // ── Foreign keys ───────────────────────────────────────
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_estimate_snapshots');
    }
};
```

Then run: `php artisan migrate`

---

## Step 2: Create the Model

Create **new file** `app/Models/Billing/CampaignEstimateSnapshot.php`:

```php
<?php

namespace App\Models\Billing;

use App\Models\Account;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * CampaignEstimateSnapshot — immutable pricing record frozen at send time.
 *
 * DATA CLASSIFICATION: Financial — Audit Evidence
 * RETENTION: 7 years (HMRC requirement)
 */
class CampaignEstimateSnapshot extends Model
{
    use HasUuids;

    protected $table = 'campaign_estimate_snapshots';

    protected $fillable = [
        'campaign_id',
        'account_id',
        'product_type',
        'campaign_type',
        'currency',
        'total_recipients',
        'estimated_cost',
        'vat_rate',
        'vat_amount',
        'estimated_cost_inc_vat',
        'reserved_amount',
        'available_balance_at_send',
        'is_postpay',
        'product_tier',
        'country_breakdown',
        'pricing_snapshot',
        'estimation_errors',
        'rcs_penetration_rate',
        'expected_rcs_count',
        'expected_sms_fallback_count',
        'reservation_id',
        'created_by',
        'snapshot_at',
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'estimated_cost' => 'decimal:4',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:4',
        'estimated_cost_inc_vat' => 'decimal:4',
        'reserved_amount' => 'decimal:4',
        'available_balance_at_send' => 'decimal:4',
        'is_postpay' => 'boolean',
        'country_breakdown' => 'array',
        'pricing_snapshot' => 'array',
        'estimation_errors' => 'array',
        'rcs_penetration_rate' => 'decimal:2',
        'expected_rcs_count' => 'integer',
        'expected_sms_fallback_count' => 'integer',
        'snapshot_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Resolve the billable product type for pricing purposes.
     * RCS Carousel is charged as RCS Single Message.
     */
    public static function resolveBillableProductType(string $campaignType): string
    {
        return match ($campaignType) {
            Campaign::TYPE_RCS_CAROUSEL => 'rcs_single',
            default => $campaignType,
        };
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'product_type' => $this->product_type,
            'campaign_type' => $this->campaign_type,
            'currency' => $this->currency,
            'total_recipients' => $this->total_recipients,
            'estimated_cost' => $this->estimated_cost,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'estimated_cost_inc_vat' => $this->estimated_cost_inc_vat,
            'reserved_amount' => $this->reserved_amount,
            'available_balance_at_send' => $this->available_balance_at_send,
            'is_postpay' => $this->is_postpay,
            'product_tier' => $this->product_tier,
            'country_breakdown' => $this->country_breakdown,
            'estimation_errors' => $this->estimation_errors,
            'rcs_penetration_rate' => $this->rcs_penetration_rate,
            'expected_rcs_count' => $this->expected_rcs_count,
            'expected_sms_fallback_count' => $this->expected_sms_fallback_count,
            'snapshot_at' => $this->snapshot_at?->toIso8601String(),
        ];
    }

    public function toSummaryArray(): array
    {
        return [
            'estimated_cost' => $this->estimated_cost,
            'vat_amount' => $this->vat_amount,
            'estimated_cost_inc_vat' => $this->estimated_cost_inc_vat,
            'reserved_amount' => $this->reserved_amount,
            'currency' => $this->currency,
            'total_recipients' => $this->total_recipients,
            'snapshot_at' => $this->snapshot_at?->toIso8601String(),
        ];
    }
}
```

---

## Step 3: Modify BillingPreflightService.php

**File:** `app/Services/Campaign/BillingPreflightService.php`

This file needs 6 changes. Do them in order.

### 3a. Add import (top of file, after existing imports)

Add this line after the other `use` statements:
```php
use App\Models\Billing\CampaignEstimateSnapshot;
```

### 3b. Add carousel mapping in `estimateCost()` method

Find this line inside `estimateCost()`:
```php
    ): CostEstimate {
        $totalCost = '0';
```

Replace with:
```php
    ): CostEstimate {
        // Carousel is charged as RCS Single Message
        $billableProductType = CampaignEstimateSnapshot::resolveBillableProductType($productType);

        $totalCost = '0';
```

Then in the same method, replace every occurrence of `$productType` that is passed to `calculateMessageCost()` or used in log context with `$billableProductType`. There are 2 places:

1. In the `calculateMessageCost()` call, change `$productType` to `$billableProductType`
2. In the `Log::warning` context array, change `'product_type' => $productType` to `'product_type' => $billableProductType`

### 3c. Add carousel mapping in `estimateCostPerSegmentGroup()` method

Same pattern. Find:
```php
    ): CostEstimate {
        $totalCost = '0';
```

Replace with:
```php
    ): CostEstimate {
        // Carousel is charged as RCS Single Message
        $billableProductType = CampaignEstimateSnapshot::resolveBillableProductType($productType);

        $totalCost = '0';
```

And replace `$productType` with `$billableProductType` in the `calculateMessageCost()` call and `Log::warning` context (same 2 places as above).

### 3d. Add carousel mapping + snapshot creation in `runPreflight()`

Find at the top of `runPreflight()`:
```php
        $account = Account::findOrFail($campaign->account_id);

        Log::info('[BillingPreflight] Running preflight', [
```

Replace with:
```php
        $account = Account::findOrFail($campaign->account_id);
        $billableProductType = CampaignEstimateSnapshot::resolveBillableProductType($campaign->type);

        Log::info('[BillingPreflight] Running preflight', [
            'campaign_id' => $campaign->id,
            'account_id' => $account->id,
            'type' => $campaign->type,
            'billable_product_type' => $billableProductType,
```

Then after Step 6 (the `$campaign->update(...)` block), add Step 7. Find this code:
```php
        // Step 6: Update campaign with cost estimate and reservation
        $campaign->update([
            'estimated_cost' => $estimate->totalCost,
            'currency' => $estimate->currency,
            'reservation_id' => $reservation->id,
        ]);

        Log::info('[BillingPreflight] Preflight complete', [
```

Replace with:
```php
        // Step 6: Update campaign with cost estimate and reservation
        $campaign->update([
            'estimated_cost' => $estimate->totalCost,
            'currency' => $estimate->currency,
            'reservation_id' => $reservation->id,
        ]);

        // Step 7: Create immutable CampaignEstimateSnapshot
        $snapshot = $this->createEstimateSnapshot(
            $campaign, $account, $estimate, $reservation->id, $billableProductType
        );

        Log::info('[BillingPreflight] Preflight complete', [
            'campaign_id' => $campaign->id,
            'estimated_cost' => $estimate->totalCost,
            'estimated_cost_inc_vat' => $snapshot->estimated_cost_inc_vat,
            'currency' => $estimate->currency,
            'reservation_id' => $reservation->id,
            'snapshot_id' => $snapshot->id,
```

Update the `PreflightResult` return to include the snapshot:
```php
        return new PreflightResult(
            approved: true,
            estimatedCost: $estimate->totalCost,
            currency: $estimate->currency,
            reservationId: $reservation->id,
            costEstimate: $estimate,
            snapshot: $snapshot,
        );
```

### 3e. Add the two new private methods

Add these two methods **before** the `releaseReservation()` method (after the closing `}` of `runPreflight()`):

```php
    /**
     * Create an immutable estimate snapshot at the moment of campaign send.
     */
    private function createEstimateSnapshot(
        Campaign $campaign,
        Account $account,
        CostEstimate $estimate,
        string $reservationId,
        string $billableProductType,
    ): CampaignEstimateSnapshot {
        $pricingSnapshot = $this->buildPricingSnapshot($account, $billableProductType, $estimate->perCountryCosts);

        // Calculate VAT
        $vatRate = $account->vat_registered ? '20.00' : '0.00';
        $vatAmount = bcmul($estimate->totalCost, bcdiv($vatRate, '100', 6), 4);
        $costIncVat = bcadd($estimate->totalCost, $vatAmount, 4);

        $totalRecipients = 0;
        foreach ($estimate->perCountryCosts as $country) {
            $totalRecipients += $country['recipient_count'];
        }

        return CampaignEstimateSnapshot::create([
            'campaign_id' => $campaign->id,
            'account_id' => $account->id,
            'product_type' => $billableProductType,
            'campaign_type' => $campaign->type,
            'currency' => $estimate->currency,
            'total_recipients' => $totalRecipients,
            'estimated_cost' => $estimate->totalCost,
            'vat_rate' => $vatRate,
            'vat_amount' => $vatAmount,
            'estimated_cost_inc_vat' => $costIncVat,
            'reserved_amount' => $estimate->totalCost,
            'available_balance_at_send' => $estimate->availableBalance,
            'is_postpay' => $estimate->isPostpay,
            'product_tier' => $account->product_tier,
            'country_breakdown' => $estimate->perCountryCosts,
            'pricing_snapshot' => $pricingSnapshot,
            'estimation_errors' => !empty($estimate->errors) ? $estimate->errors : null,
            'reservation_id' => $reservationId,
            'created_by' => $campaign->created_by,
            'snapshot_at' => now(),
        ]);
    }

    /**
     * Build a snapshot of the exact prices resolved for each country.
     */
    private function buildPricingSnapshot(Account $account, string $billableProductType, array $perCountryCosts): array
    {
        $snapshot = [];

        foreach ($perCountryCosts as $countryKey => $data) {
            $countryIso = $data['country_iso'] ?? null;

            try {
                $price = $this->pricingEngine->resolvePrice($account, $billableProductType, $countryIso);

                $snapshot[] = [
                    'product_type' => $billableProductType,
                    'country_iso' => $countryIso,
                    'unit_price' => $price->unitPrice,
                    'currency' => $price->currency,
                    'source' => $price->source,
                    'price_id' => $price->priceId,
                ];
            } catch (\App\Exceptions\Billing\PriceNotFoundException $e) {
                $snapshot[] = [
                    'product_type' => $billableProductType,
                    'country_iso' => $countryIso,
                    'unit_price' => null,
                    'currency' => $account->currency ?? 'GBP',
                    'source' => 'not_found',
                    'price_id' => null,
                ];
            }
        }

        return $snapshot;
    }
```

### 3f. Update PreflightResult DTO

Find the `PreflightResult` class (at the bottom of the same file). Change its constructor to add the snapshot parameter:

```php
class PreflightResult
{
    public function __construct(
        public readonly bool $approved,
        public readonly string $estimatedCost,
        public readonly string $currency,
        public readonly ?string $reservationId,
        public readonly CostEstimate $costEstimate,
        public readonly ?CampaignEstimateSnapshot $snapshot = null,
    ) {}

    public function toArray(): array
    {
        return [
            'approved' => $this->approved,
            'estimated_cost' => $this->estimatedCost,
            'currency' => $this->currency,
            'reservation_id' => $this->reservationId,
            'cost_estimate' => $this->costEstimate->toArray(),
            'snapshot' => $this->snapshot?->toPortalArray(),
        ];
    }
}
```

---

## Step 4: Modify DeliveryService.php

**File:** `app/Services/Campaign/DeliveryService.php`

### 4a. Add import (top of file)

```php
use App\Models\Billing\CampaignEstimateSnapshot;
```

### 4b. Add carousel mapping in `calculateRecipientCost()`

Find this code inside `calculateRecipientCost()`:
```php
    ): string {
        try {
            $calculation = $this->pricingEngine->calculateMessageCost(
                $account,
                $campaign->type,
```

Replace with:
```php
    ): string {
        try {
            // Carousel is charged as RCS Single Message
            $billableProductType = CampaignEstimateSnapshot::resolveBillableProductType($campaign->type);

            $calculation = $this->pricingEngine->calculateMessageCost(
                $account,
                $billableProductType,
```

That's it — no other changes to this file.

---

## Step 5: Modify LedgerService.php

**File:** `app/Services/Billing/LedgerService.php`

Find the `revenueAccountForProduct()` method. Currently it has:
```php
'rcs_basic', 'rcs_single' => LedgerAccount::REVENUE_RCS,
```

Change to:
```php
'rcs_basic', 'rcs_single', 'rcs_carousel' => LedgerAccount::REVENUE_RCS,
```

That's it — one word added.

---

## Step 6: Modify Campaign.php Model

**File:** `app/Models/Campaign.php`

### 6a. Add import (top of file)

```php
use App\Models\Billing\CampaignEstimateSnapshot;
```

### 6b. Add relationship

Find the `recipients()` relationship:
```php
    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }
```

Add this **immediately after** it (before `optOutNumber()`):
```php
    public function estimateSnapshot(): HasOne
    {
        return $this->hasOne(CampaignEstimateSnapshot::class, 'campaign_id');
    }
```

**Note:** Make sure `HasOne` is imported at the top of the file. If only `HasMany` and `BelongsTo` are imported, add:
```php
use Illuminate\Database\Eloquent\Relations\HasOne;
```

### 6c. Add to toPortalArray()

Find this section in `toPortalArray()`:
```php
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'currency' => $this->currency,
            'content_resolved_at' => $this->content_resolved_at?->toIso8601String(),
```

Insert between `'currency'` and `'content_resolved_at'`:
```php
            'estimated_cost' => $this->estimated_cost,
            'actual_cost' => $this->actual_cost,
            'currency' => $this->currency,
            'estimate_snapshot' => $this->relationLoaded('estimateSnapshot')
                ? $this->estimateSnapshot?->toPortalArray()
                : null,
            'content_resolved_at' => $this->content_resolved_at?->toIso8601String(),
```

---

## Summary of All Changes

| # | File | Action | What |
|---|------|--------|------|
| 1 | `database/migrations/2026_03_02_000001_*` | **CREATE** | New migration for `campaign_estimate_snapshots` table |
| 2 | `app/Models/Billing/CampaignEstimateSnapshot.php` | **CREATE** | New model with `resolveBillableProductType()` and portal serialisation |
| 3 | `app/Services/Campaign/BillingPreflightService.php` | **MODIFY** | Add carousel product type mapping + snapshot creation in `runPreflight()` |
| 4 | `app/Services/Campaign/DeliveryService.php` | **MODIFY** | Add carousel product type mapping in `calculateRecipientCost()` |
| 5 | `app/Services/Billing/LedgerService.php` | **MODIFY** | Add `'rcs_carousel'` to revenue account mapping (1 word) |
| 6 | `app/Models/Campaign.php` | **MODIFY** | Add `estimateSnapshot()` relationship + include in `toPortalArray()` |

## What NOT to Change

- **Do NOT modify** `CampaignApiController.php`, `QuickSMSController.php`, or `CampaignService.php`
- **Do NOT modify** `RecipientResolverService.php`, `OptOutService.php`, or any RCS service
- **Do NOT modify** any Blade views (`send-message.blade.php`, `confirm-campaign.blade.php`)
- **Do NOT modify** routes, middleware, or any other controller
- **Do NOT modify** any other migration or model file
- **Do NOT add** new packages, dependencies, or npm modules

---

## Verification

After making all changes:

```bash
php artisan migrate
```

This should create the `campaign_estimate_snapshots` table without errors.

### Quick Check

1. The `campaign_estimate_snapshots` table exists with all columns listed above
2. `CampaignEstimateSnapshot::resolveBillableProductType('rcs_carousel')` returns `'rcs_single'`
3. `CampaignEstimateSnapshot::resolveBillableProductType('sms')` returns `'sms'` (unchanged)
4. The `LedgerService::revenueAccountForProduct('rcs_carousel')` returns `REVENUE_RCS`
5. `Campaign::find($id)->estimateSnapshot` returns the snapshot relationship
6. The `BillingPreflightService::runPreflight()` creates a snapshot row when a campaign is sent

### What Should Happen at Send Time

When a user sends a campaign:
1. `BillingPreflightService::runPreflight()` estimates cost
2. It reserves funds
3. It creates a `CampaignEstimateSnapshot` row with the exact pricing
4. The snapshot includes VAT calculation, per-country breakdown, and the exact rate used
5. This snapshot is immutable — it never changes after creation

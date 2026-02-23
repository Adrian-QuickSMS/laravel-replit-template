<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Billing\CustomerPrice;
use App\Models\Billing\PricingChangeLog;
use App\Models\Billing\PricingEvent;
use App\Models\Billing\PricingEventItem;
use App\Models\Billing\ProductTierPrice;
use App\Models\Billing\ServiceCatalogue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin Pricing Management Controller
 *
 * Manages the service catalogue, tier pricing grid (Starter/Enterprise),
 * scheduled pricing events, price history, and CSV export.
 *
 * Route prefix: /admin/management/pricing (view)
 * API prefix: /admin/api/pricing (JSON endpoints)
 */
class PricingManagementController extends Controller
{
    // =====================================================
    // VIEW ROUTE
    // =====================================================

    /**
     * Pricing management page
     */
    public function index()
    {
        return view('admin.management.pricing', [
            'page_title' => 'Pricing Management',
        ]);
    }

    // =====================================================
    // SERVICE CATALOGUE
    // =====================================================

    /**
     * List all services in the catalogue
     * GET /admin/api/pricing/services
     */
    public function services(Request $request): JsonResponse
    {
        $query = ServiceCatalogue::query()->ordered();

        if ($request->has('active_only')) {
            $query->active();
        }

        if ($tier = $request->input('tier')) {
            $query->forTier($tier);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Add a new service to the catalogue
     * POST /admin/api/pricing/services
     */
    public function storeService(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:50|unique:service_catalogue,slug|regex:/^[a-z0-9_]+$/',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'unit_label' => 'required|string|max:30',
            'display_format' => 'required|in:pence,pounds',
            'decimal_places' => 'required|integer|min:0|max:6',
            'is_per_message' => 'sometimes|boolean',
            'is_recurring' => 'sometimes|boolean',
            'is_one_off' => 'sometimes|boolean',
            'available_on_starter' => 'sometimes|boolean',
            'available_on_enterprise' => 'sometimes|boolean',
            'bespoke_only' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $service = ServiceCatalogue::create($validated);

        $this->logGovernanceEvent(
            'SERVICE_CATALOGUE_CREATED',
            'service_catalogue',
            $service->id,
            null,
            $service->toArray(),
            $request
        );

        return response()->json([
            'success' => true,
            'data' => $service,
            'message' => "Service '{$service->display_name}' added to catalogue.",
        ], 201);
    }

    /**
     * Update a service in the catalogue
     * PUT /admin/api/pricing/services/{id}
     */
    public function updateService(Request $request, int $id): JsonResponse
    {
        $service = ServiceCatalogue::findOrFail($id);
        $before = $service->toArray();

        $validated = $request->validate([
            'display_name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:1000',
            'unit_label' => 'sometimes|string|max:30',
            'display_format' => 'sometimes|in:pence,pounds',
            'decimal_places' => 'sometimes|integer|min:0|max:6',
            'is_per_message' => 'sometimes|boolean',
            'is_recurring' => 'sometimes|boolean',
            'is_one_off' => 'sometimes|boolean',
            'available_on_starter' => 'sometimes|boolean',
            'available_on_enterprise' => 'sometimes|boolean',
            'bespoke_only' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $service->update($validated);

        $this->logGovernanceEvent(
            'SERVICE_CATALOGUE_UPDATED',
            'service_catalogue',
            $service->id,
            $before,
            $service->fresh()->toArray(),
            $request
        );

        return response()->json([
            'success' => true,
            'data' => $service->fresh(),
            'message' => "Service '{$service->display_name}' updated.",
        ]);
    }

    // =====================================================
    // TIER PRICING GRID
    // =====================================================

    /**
     * Get current tier pricing grid (services × tiers)
     * GET /admin/api/pricing/current
     */
    public function currentPricing(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());

        $services = ServiceCatalogue::active()
            ->where('bespoke_only', false)
            ->ordered()
            ->get();

        $prices = ProductTierPrice::with('service')
            ->active()
            ->validAt($date)
            ->whereNull('country_iso')
            ->get()
            ->groupBy(function ($p) {
                return $p->product_tier . '.' . $p->product_type;
            });

        $grid = [];
        foreach ($services as $service) {
            $row = [
                'service' => $service,
                'starter' => null,
                'enterprise' => null,
            ];

            foreach (['starter', 'enterprise'] as $tier) {
                $key = $tier . '.' . $service->slug;
                $price = $prices->get($key)?->first();
                if ($price) {
                    $row[$tier] = [
                        'id' => $price->id,
                        'unit_price' => $price->unit_price,
                        'valid_from' => $price->valid_from?->toDateString(),
                        'valid_to' => $price->valid_to?->toDateString(),
                    ];
                }
            }

            $grid[] = $row;
        }

        // Count affected accounts
        $starterCount = Account::where('product_tier', 'starter')->count();
        $enterpriseCount = Account::where('product_tier', 'enterprise')->count();
        $bespokeCount = Account::where('product_tier', 'bespoke')->count();

        return response()->json([
            'success' => true,
            'data' => $grid,
            'date' => $date,
            'account_counts' => [
                'starter' => $starterCount,
                'enterprise' => $enterpriseCount,
                'bespoke_unaffected' => $bespokeCount,
            ],
        ]);
    }

    /**
     * Preview pricing at a specific future date
     * GET /admin/api/pricing/preview?date=YYYY-MM-DD
     */
    public function previewPricing(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date']);
        $request->merge(['date' => $request->input('date')]);
        return $this->currentPricing($request);
    }

    /**
     * Update a single tier price (immediate or scheduled)
     * PUT /admin/api/pricing/tier-prices
     */
    public function updateTierPrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_catalogue_id' => 'required|exists:service_catalogue,id',
            'tier' => 'required|in:starter,enterprise',
            'unit_price' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        $service = ServiceCatalogue::findOrFail($validated['service_catalogue_id']);

        try {
            DB::beginTransaction();

            // Find current active price for this tier+service
            $currentPrice = ProductTierPrice::where('product_tier', $validated['tier'])
                ->where('product_type', $service->slug)
                ->whereNull('country_iso')
                ->active()
                ->validAt()
                ->first();

            $oldPrice = $currentPrice?->unit_price;
            $effectiveFrom = $validated['effective_from'];

            // If effective date is today or past, apply immediately
            if ($effectiveFrom <= now()->toDateString()) {
                // Close out old price
                if ($currentPrice) {
                    $currentPrice->update([
                        'valid_to' => now()->subDay()->toDateString(),
                        'active' => $effectiveFrom <= now()->toDateString(),
                    ]);
                }

                $newRow = ProductTierPrice::create([
                    'product_tier' => $validated['tier'],
                    'product_type' => $service->slug,
                    'service_catalogue_id' => $service->id,
                    'country_iso' => null,
                    'unit_price' => $validated['unit_price'],
                    'currency' => 'GBP',
                    'valid_from' => $effectiveFrom,
                    'valid_to' => null,
                    'active' => true,
                    'created_by' => $request->user()->id ?? null,
                ]);
            } else {
                // Schedule: set valid_to on current price, create future row
                if ($currentPrice) {
                    $currentPrice->update([
                        'valid_to' => \Carbon\Carbon::parse($effectiveFrom)->subDay()->toDateString(),
                    ]);
                }

                $newRow = ProductTierPrice::create([
                    'product_tier' => $validated['tier'],
                    'product_type' => $service->slug,
                    'service_catalogue_id' => $service->id,
                    'country_iso' => null,
                    'unit_price' => $validated['unit_price'],
                    'currency' => 'GBP',
                    'valid_from' => $effectiveFrom,
                    'valid_to' => null,
                    'active' => true,
                    'created_by' => $request->user()->id ?? null,
                ]);
            }

            // Log the change
            PricingChangeLog::create([
                'service_catalogue_id' => $service->id,
                'tier' => $validated['tier'],
                'account_id' => null,
                'country_iso' => null,
                'old_price' => $oldPrice,
                'new_price' => $validated['unit_price'],
                'currency' => 'GBP',
                'effective_from' => $effectiveFrom,
                'source' => 'admin',
                'reason' => $validated['reason'] ?? null,
                'changed_by' => $request->user()->id ?? null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $this->logGovernanceEvent(
                'TIER_PRICE_UPDATED',
                'product_tier_prices',
                $newRow->id,
                ['unit_price' => $oldPrice, 'tier' => $validated['tier'], 'service' => $service->slug],
                ['unit_price' => $validated['unit_price'], 'effective_from' => $effectiveFrom],
                $request,
                $validated['reason'] ?? null
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $newRow,
                'message' => $effectiveFrom <= now()->toDateString()
                    ? "{$service->display_name} {$validated['tier']} price updated to {$validated['unit_price']}."
                    : "{$service->display_name} {$validated['tier']} price scheduled for {$effectiveFrom}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[PricingManagement] Failed to update tier price', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to update price.',
            ], 500);
        }
    }

    // =====================================================
    // PRICING EVENTS
    // =====================================================

    /**
     * List pricing events
     * GET /admin/api/pricing/events
     */
    public function events(Request $request): JsonResponse
    {
        $query = PricingEvent::with(['items.service', 'createdBy:id,email,first_name,last_name']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $events = $query->orderBy('effective_date', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * Create a pricing event
     * POST /admin/api/pricing/events
     */
    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'effective_date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.service_catalogue_id' => 'required|exists:service_catalogue,id',
            'items.*.tier' => 'required|in:starter,enterprise',
            'items.*.new_price' => 'required|numeric|min:0',
            'items.*.country_iso' => 'nullable|string|size:2',
        ]);

        try {
            DB::beginTransaction();

            $event = PricingEvent::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'effective_date' => $validated['effective_date'],
                'reason' => $validated['reason'] ?? null,
                'status' => PricingEvent::STATUS_DRAFT,
                'created_by' => $request->user()->id ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $service = ServiceCatalogue::find($item['service_catalogue_id']);

                // Look up current price
                $currentPrice = ProductTierPrice::where('product_tier', $item['tier'])
                    ->where('product_type', $service->slug)
                    ->where('country_iso', $item['country_iso'] ?? null)
                    ->active()
                    ->validAt()
                    ->first();

                PricingEventItem::create([
                    'pricing_event_id' => $event->id,
                    'service_catalogue_id' => $item['service_catalogue_id'],
                    'tier' => $item['tier'],
                    'country_iso' => $item['country_iso'] ?? null,
                    'old_price' => $currentPrice?->unit_price,
                    'new_price' => $item['new_price'],
                    'currency' => 'GBP',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $event->load('items.service'),
                'message' => "Pricing event '{$event->name}' created as draft.",
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[PricingManagement] Failed to create pricing event', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to create pricing event.',
            ], 500);
        }
    }

    /**
     * Get single pricing event detail
     * GET /admin/api/pricing/events/{id}
     */
    public function showEvent(string $id): JsonResponse
    {
        $event = PricingEvent::with(['items.service', 'createdBy:id,email,first_name,last_name'])
            ->findOrFail($id);

        $affectedCounts = $event->getAffectedAccountCounts();

        return response()->json([
            'success' => true,
            'data' => $event,
            'affected_accounts' => $affectedCounts,
        ]);
    }

    /**
     * Update a pricing event (add/remove items, change date)
     * PUT /admin/api/pricing/events/{id}
     */
    public function updateEvent(Request $request, string $id): JsonResponse
    {
        $event = PricingEvent::findOrFail($id);

        if (!$event->canEdit()) {
            return response()->json([
                'success' => false,
                'error' => 'This pricing event cannot be modified.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:200',
            'description' => 'nullable|string|max:2000',
            'effective_date' => 'sometimes|date|after_or_equal:today',
            'reason' => 'nullable|string|max:2000',
            'items' => 'sometimes|array|min:1',
            'items.*.service_catalogue_id' => 'required_with:items|exists:service_catalogue,id',
            'items.*.tier' => 'required_with:items|in:starter,enterprise',
            'items.*.new_price' => 'required_with:items|numeric|min:0',
            'items.*.country_iso' => 'nullable|string|size:2',
        ]);

        try {
            DB::beginTransaction();

            $event->update(collect($validated)->except('items')->toArray());

            // Replace items if provided
            if (isset($validated['items'])) {
                $event->items()->delete();

                foreach ($validated['items'] as $item) {
                    $service = ServiceCatalogue::find($item['service_catalogue_id']);

                    $currentPrice = ProductTierPrice::where('product_tier', $item['tier'])
                        ->where('product_type', $service->slug)
                        ->where('country_iso', $item['country_iso'] ?? null)
                        ->active()
                        ->validAt()
                        ->first();

                    PricingEventItem::create([
                        'pricing_event_id' => $event->id,
                        'service_catalogue_id' => $item['service_catalogue_id'],
                        'tier' => $item['tier'],
                        'country_iso' => $item['country_iso'] ?? null,
                        'old_price' => $currentPrice?->unit_price,
                        'new_price' => $item['new_price'],
                        'currency' => 'GBP',
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $event->fresh()->load('items.service'),
                'message' => 'Pricing event updated.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[PricingManagement] Failed to update pricing event', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to update pricing event.',
            ], 500);
        }
    }

    /**
     * Schedule a draft event (draft → scheduled)
     * POST /admin/api/pricing/events/{id}/schedule
     */
    public function scheduleEvent(Request $request, string $id): JsonResponse
    {
        $event = PricingEvent::findOrFail($id);

        if (!$event->isDraft()) {
            return response()->json([
                'success' => false,
                'error' => 'Only draft events can be scheduled.',
            ], 422);
        }

        if ($event->items()->count() === 0) {
            return response()->json([
                'success' => false,
                'error' => 'Cannot schedule an event with no price changes.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            $event->update(['status' => PricingEvent::STATUS_SCHEDULED]);

            // Create the future price rows in product_tier_prices
            foreach ($event->items as $item) {
                $service = ServiceCatalogue::find($item->service_catalogue_id);

                // Close current price at day before effective date
                $currentPrice = ProductTierPrice::where('product_tier', $item->tier)
                    ->where('product_type', $service->slug)
                    ->where('country_iso', $item->country_iso)
                    ->active()
                    ->validAt()
                    ->first();

                if ($currentPrice) {
                    $currentPrice->update([
                        'valid_to' => \Carbon\Carbon::parse($event->effective_date)->subDay()->toDateString(),
                    ]);
                }

                // Create scheduled price row
                ProductTierPrice::create([
                    'product_tier' => $item->tier,
                    'product_type' => $service->slug,
                    'service_catalogue_id' => $service->id,
                    'pricing_event_id' => $event->id,
                    'country_iso' => $item->country_iso,
                    'unit_price' => $item->new_price,
                    'currency' => $item->currency,
                    'valid_from' => $event->effective_date->toDateString(),
                    'valid_to' => null,
                    'active' => true,
                    'created_by' => $request->user()->id ?? null,
                ]);

                // Log the scheduled change
                PricingChangeLog::create([
                    'service_catalogue_id' => $service->id,
                    'tier' => $item->tier,
                    'country_iso' => $item->country_iso,
                    'old_price' => $item->old_price,
                    'new_price' => $item->new_price,
                    'currency' => $item->currency,
                    'effective_from' => $event->effective_date->toDateString(),
                    'source' => 'scheduled_event',
                    'pricing_event_id' => $event->id,
                    'reason' => $event->reason,
                    'changed_by' => $request->user()->id ?? null,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            $this->logGovernanceEvent(
                'PRICING_EVENT_SCHEDULED',
                'pricing_events',
                $event->id,
                ['status' => 'draft'],
                ['status' => 'scheduled', 'effective_date' => $event->effective_date->toDateString()],
                $request,
                $event->reason
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $event->fresh()->load('items.service'),
                'message' => "Pricing event scheduled for {$event->effective_date->toDateString()}.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[PricingManagement] Failed to schedule pricing event', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to schedule pricing event.',
            ], 500);
        }
    }

    /**
     * Cancel a pricing event (draft/scheduled → cancelled)
     * POST /admin/api/pricing/events/{id}/cancel
     */
    public function cancelEvent(Request $request, string $id): JsonResponse
    {
        $event = PricingEvent::findOrFail($id);

        if (!$event->canCancel()) {
            return response()->json([
                'success' => false,
                'error' => 'This event cannot be cancelled.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // If scheduled, revert the price rows
            if ($event->isScheduled()) {
                // Remove future price rows linked to this event
                ProductTierPrice::where('pricing_event_id', $event->id)->delete();

                // Reopen the valid_to on current prices
                foreach ($event->items as $item) {
                    $service = ServiceCatalogue::find($item->service_catalogue_id);

                    $closedPrice = ProductTierPrice::where('product_tier', $item->tier)
                        ->where('product_type', $service->slug)
                        ->where('country_iso', $item->country_iso)
                        ->where('valid_to', \Carbon\Carbon::parse($event->effective_date)->subDay()->toDateString())
                        ->active()
                        ->first();

                    if ($closedPrice) {
                        $closedPrice->update(['valid_to' => null]);
                    }
                }
            }

            $event->update([
                'status' => PricingEvent::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id ?? null,
            ]);

            $this->logGovernanceEvent(
                'PRICING_EVENT_CANCELLED',
                'pricing_events',
                $event->id,
                ['status' => $event->getOriginal('status')],
                ['status' => 'cancelled'],
                $request
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $event->fresh(),
                'message' => "Pricing event '{$event->name}' cancelled.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[PricingManagement] Failed to cancel pricing event', [
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to cancel pricing event.',
            ], 500);
        }
    }

    // =====================================================
    // PRICE HISTORY
    // =====================================================

    /**
     * Get price change history (with filters)
     * GET /admin/api/pricing/history
     */
    public function history(Request $request): JsonResponse
    {
        $query = PricingChangeLog::with(['service', 'changedBy:id,email,first_name,last_name', 'pricingEvent:id,name']);

        if ($serviceId = $request->input('service_catalogue_id')) {
            $query->where('service_catalogue_id', $serviceId);
        }

        if ($tier = $request->input('tier')) {
            $query->where('tier', $tier);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        if ($from = $request->input('from_date')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->input('to_date')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $history = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    // =====================================================
    // EXPORT
    // =====================================================

    /**
     * Export pricing as CSV
     * GET /admin/api/pricing/export
     *
     * Includes past, current, and scheduled future prices.
     */
    public function export(Request $request): StreamedResponse
    {
        $filename = 'quicksms-pricing-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');

            // Header row
            fputcsv($handle, [
                'Service',
                'Tier',
                'Country',
                'Unit Price',
                'Currency',
                'Display Format',
                'Valid From',
                'Valid To',
                'Status',
                'Pricing Event',
                'Source',
            ]);

            // All tier prices (past, current, future)
            $prices = ProductTierPrice::with('service', 'pricingEvent')
                ->orderBy('product_type')
                ->orderBy('product_tier')
                ->orderBy('valid_from', 'desc')
                ->get();

            $today = now()->toDateString();

            foreach ($prices as $p) {
                $status = 'past';
                if ($p->active && ($p->valid_to === null || $p->valid_to->toDateString() >= $today)) {
                    if ($p->valid_from->toDateString() > $today) {
                        $status = 'scheduled';
                    } else {
                        $status = 'current';
                    }
                }

                fputcsv($handle, [
                    $p->service?->display_name ?? $p->product_type,
                    ucfirst($p->product_tier),
                    $p->country_iso ?? 'Default',
                    $p->unit_price,
                    $p->currency,
                    $p->service?->display_format ?? 'pence',
                    $p->valid_from?->toDateString(),
                    $p->valid_to?->toDateString() ?? 'Open',
                    $status,
                    $p->pricingEvent?->name ?? '',
                    'tier',
                ]);
            }

            // Price change history
            fputcsv($handle, []); // blank row separator
            fputcsv($handle, ['--- CHANGE HISTORY ---']);
            fputcsv($handle, [
                'Service',
                'Tier',
                'Account ID',
                'Country',
                'Old Price',
                'New Price',
                'Currency',
                'Effective From',
                'Source',
                'Reason',
                'Changed By',
                'Date',
            ]);

            PricingChangeLog::with(['service', 'changedBy'])
                ->orderBy('created_at', 'desc')
                ->chunk(500, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->service?->display_name ?? '',
                            $log->tier ?? 'bespoke',
                            $log->account_id ?? '',
                            $log->country_iso ?? 'Default',
                            $log->old_price ?? 'N/A',
                            $log->new_price,
                            $log->currency,
                            $log->effective_from?->toDateString(),
                            $log->source,
                            $log->reason ?? '',
                            $log->changedBy ? ($log->changedBy->first_name . ' ' . $log->changedBy->last_name) : '',
                            $log->created_at?->toIso8601String(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    // =====================================================
    // SCHEDULED EVENTS UPCOMING LIST
    // =====================================================

    /**
     * Get upcoming scheduled price changes
     * GET /admin/api/pricing/upcoming
     */
    public function upcoming(): JsonResponse
    {
        $events = PricingEvent::with('items.service')
            ->whereIn('status', [PricingEvent::STATUS_DRAFT, PricingEvent::STATUS_SCHEDULED])
            ->orderBy('effective_date')
            ->get();

        // Also get individually scheduled prices not part of events
        $scheduledPrices = ProductTierPrice::with('service')
            ->active()
            ->future()
            ->whereNull('pricing_event_id')
            ->orderBy('valid_from')
            ->get();

        return response()->json([
            'success' => true,
            'events' => $events,
            'individual_scheduled' => $scheduledPrices,
        ]);
    }

    // =====================================================
    // PRIVATE HELPERS
    // =====================================================

    private function logGovernanceEvent(
        string $eventType,
        string $entityType,
        $entityId,
        $beforeState,
        $afterState,
        Request $request,
        ?string $reason = null
    ): void {
        try {
            DB::statement('SAVEPOINT governance_log');
            DB::statement(
                'INSERT INTO governance_audit_events (event_uuid, event_type, entity_type, entity_id, account_id, sub_account_id, actor_id, actor_type, actor_email, before_state, after_state, reason, source_ip, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    Str::uuid()->toString(),
                    $eventType,
                    $entityType,
                    0,
                    null,
                    null,
                    session('admin_auth.id') ?? $request->user()->id ?? '00000000-0000-0000-0000-000000000000',
                    'ADMIN',
                    session('admin_auth.email') ?? $request->user()->email ?? 'admin@quicksms.co.uk',
                    json_encode(array_merge((array) $beforeState, ['entity_uuid' => (string) $entityId])),
                    json_encode($afterState),
                    $reason,
                    $request->ip(),
                    $request->userAgent(),
                    now(),
                ]
            );
            DB::statement('RELEASE SAVEPOINT governance_log');
        } catch (\Exception $e) {
            try { DB::statement('ROLLBACK TO SAVEPOINT governance_log'); } catch (\Exception $ignored) {}
            Log::warning('[PricingManagement] Governance audit skipped: ' . $e->getMessage());
        }
    }
}

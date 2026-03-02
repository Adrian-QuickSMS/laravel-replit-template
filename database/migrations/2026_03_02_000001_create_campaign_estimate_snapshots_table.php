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
            // Each entry: { country_iso, recipient_count, unit_price, segments,
            //               cost_per_message, total_cost, currency, price_source }
            $table->jsonb('country_breakdown');

            // ── Pricing snapshot ────────────────────────────────────
            // Captures the exact price resolved for each product/country
            // at the moment of estimation, so we can prove what rate was used.
            // Each entry: { product_type, country_iso, unit_price, currency,
            //               source (tier_starter|admin_override|hubspot|etc), price_id }
            $table->jsonb('pricing_snapshot');

            // ── Errors / warnings at estimation time ───────────────
            $table->jsonb('estimation_errors')->nullable();

            // ── RCS-specific fields ────────────────────────────────
            $table->decimal('rcs_penetration_rate', 5, 2)->nullable(); // % expected to deliver via RCS
            $table->integer('expected_rcs_count')->nullable();
            $table->integer('expected_sms_fallback_count')->nullable();

            // ── Metadata ───────────────────────────────────────────
            $table->uuid('reservation_id')->nullable();
            $table->string('created_by')->nullable();        // user who triggered send
            $table->timestamp('snapshot_at');                 // when snapshot was taken
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

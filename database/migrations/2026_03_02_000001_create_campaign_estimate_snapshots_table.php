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

            $table->string('product_type', 30);
            $table->string('campaign_type', 30);
            $table->string('currency', 3)->default('GBP');

            $table->integer('total_recipients');
            $table->decimal('estimated_cost', 14, 4);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('vat_amount', 14, 4)->default(0);
            $table->decimal('estimated_cost_inc_vat', 14, 4);
            $table->decimal('reserved_amount', 14, 4);

            $table->decimal('available_balance_at_send', 14, 4);
            $table->boolean('is_postpay')->default(false);
            $table->string('product_tier', 20)->nullable();

            $table->jsonb('country_breakdown');

            $table->jsonb('pricing_snapshot');

            $table->jsonb('estimation_errors')->nullable();

            $table->decimal('rcs_penetration_rate', 5, 2)->nullable();
            $table->integer('expected_rcs_count')->nullable();
            $table->integer('expected_sms_fallback_count')->nullable();

            $table->uuid('reservation_id')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('snapshot_at');
            $table->timestamps();

            $table->index('account_id');
            $table->index('snapshot_at');

            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_estimate_snapshots');
    }
};

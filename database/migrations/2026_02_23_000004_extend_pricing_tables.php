<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Extends existing pricing tables:
 *
 * 1. Adds new values to billable_product_type ENUM
 * 2. Adds service_catalogue_id FK to product_tier_prices
 * 3. Adds pricing_event_id FK to product_tier_prices
 * 4. Adds service_catalogue_id FK to customer_prices
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add new values to billable_product_type ENUM
        // PostgreSQL allows adding values to existing ENUMs
        DB::statement("ALTER TYPE billable_product_type ADD VALUE IF NOT EXISTS 'sms_international'");
        DB::statement("ALTER TYPE billable_product_type ADD VALUE IF NOT EXISTS 'virtual_number_setup'");
        DB::statement("ALTER TYPE billable_product_type ADD VALUE IF NOT EXISTS 'shortcode_setup'");
        DB::statement("ALTER TYPE billable_product_type ADD VALUE IF NOT EXISTS 'shortcode_inbound_sms'");
        DB::statement("ALTER TYPE billable_product_type ADD VALUE IF NOT EXISTS 'shortcode_keyword'");

        // Add service_catalogue_id and pricing_event_id to product_tier_prices
        Schema::table('product_tier_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('service_catalogue_id')->nullable()->after('id');
            $table->uuid('pricing_event_id')->nullable();

            $table->foreign('service_catalogue_id')->references('id')->on('service_catalogue')->onDelete('set null');
            $table->foreign('pricing_event_id')->references('id')->on('pricing_events')->onDelete('set null');

            $table->index('service_catalogue_id');
            $table->index('pricing_event_id');
        });

        // Add service_catalogue_id to customer_prices
        Schema::table('customer_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('service_catalogue_id')->nullable()->after('id');

            $table->foreign('service_catalogue_id')->references('id')->on('service_catalogue')->onDelete('set null');

            $table->index('service_catalogue_id');
        });

        // Backfill service_catalogue_id on existing product_tier_prices rows
        DB::statement("
            UPDATE product_tier_prices ptp
            SET service_catalogue_id = sc.id
            FROM service_catalogue sc
            WHERE ptp.product_type::text = sc.slug
              AND ptp.service_catalogue_id IS NULL
        ");

        // Backfill service_catalogue_id on existing customer_prices rows
        DB::statement("
            UPDATE customer_prices cp
            SET service_catalogue_id = sc.id
            FROM service_catalogue sc
            WHERE cp.product_type::text = sc.slug
              AND cp.service_catalogue_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('customer_prices', function (Blueprint $table) {
            $table->dropForeign(['service_catalogue_id']);
            $table->dropIndex(['service_catalogue_id']);
            $table->dropColumn('service_catalogue_id');
        });

        Schema::table('product_tier_prices', function (Blueprint $table) {
            $table->dropForeign(['service_catalogue_id']);
            $table->dropForeign(['pricing_event_id']);
            $table->dropIndex(['service_catalogue_id']);
            $table->dropIndex(['pricing_event_id']);
            $table->dropColumn(['service_catalogue_id', 'pricing_event_id']);
        });

        // Note: Cannot remove ENUM values in PostgreSQL without recreating the type
    }
};

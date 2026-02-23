<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Pricing Change Log — full audit trail for all pricing changes
 *
 * Records every price modification regardless of source (admin, HubSpot, scheduled event).
 * Includes both tier-level changes and bespoke/customer-level changes.
 * Also tracks HubSpot sync conflicts for visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_change_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('service_catalogue_id');
            $table->string('tier', 20)->nullable()->comment('NULL for bespoke/customer changes');
            $table->uuid('account_id')->nullable()->comment('NULL for tier-level changes');
            $table->string('country_iso', 2)->nullable();
            $table->decimal('old_price', 10, 6)->nullable()->comment('NULL if first price');
            $table->decimal('new_price', 10, 6);
            $table->string('currency', 3)->default('GBP');
            $table->date('effective_from');
            $table->string('source', 30)->comment('admin, hubspot, scheduled_event');
            $table->uuid('pricing_event_id')->nullable()->comment('FK if part of a grouped event');
            $table->text('reason')->nullable();
            $table->uuid('changed_by')->nullable()->comment('Admin user who made the change');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('hubspot_deal_id')->nullable();
            $table->boolean('is_conflict')->default(false)->comment('HubSpot sync conflict detected');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('service_catalogue_id')->references('id')->on('service_catalogue');
            $table->foreign('pricing_event_id')->references('id')->on('pricing_events');

            $table->index(['service_catalogue_id', 'tier', 'created_at']);
            $table->index(['account_id', 'created_at']);
            $table->index('pricing_event_id');
            $table->index('created_at');
            $table->index('source');
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_pricing_change_log()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER before_insert_pricing_change_log_uuid
            BEFORE INSERT ON pricing_change_log
            FOR EACH ROW EXECUTE FUNCTION generate_uuid_pricing_change_log();
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_pricing_change_log_uuid ON pricing_change_log");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_pricing_change_log()");
        Schema::dropIfExists('pricing_change_log');
    }
};

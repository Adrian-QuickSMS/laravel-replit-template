<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Pricing Events — grouped pricing changes with scheduled effective dates
 *
 * Supports both individual price changes (via product_tier_prices.valid_from)
 * and grouped events (e.g., "Q2 2026 Price Update") that batch multiple
 * price changes together for audit and communication purposes.
 *
 * Events transition: draft → scheduled → applied (automatic at midnight)
 * or draft/scheduled → cancelled
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE pricing_event_status AS ENUM ('draft', 'scheduled', 'applied', 'cancelled')");

        Schema::create('pricing_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 200)->comment('e.g., Q2 2026 Price Update');
            $table->text('description')->nullable();
            $table->date('effective_date');
            $table->text('reason')->nullable()->comment('Business reason for the change');
            $table->uuid('created_by')->nullable();
            $table->timestamp('applied_at')->nullable()->comment('When the event was actually applied');
            $table->timestamp('cancelled_at')->nullable();
            $table->uuid('cancelled_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('effective_date');
        });

        DB::statement("ALTER TABLE pricing_events ADD COLUMN status pricing_event_status NOT NULL DEFAULT 'draft'");
        DB::statement("CREATE INDEX idx_pricing_events_status ON pricing_events (status)");
        DB::statement("CREATE INDEX idx_pricing_events_effective ON pricing_events (status, effective_date)");

        // Individual price changes within an event
        Schema::create('pricing_event_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pricing_event_id');
            $table->unsignedBigInteger('service_catalogue_id');
            $table->string('tier', 20)->comment('starter, enterprise');
            $table->string('country_iso', 2)->nullable()->comment('NULL = default/no country');
            $table->decimal('old_price', 10, 6)->nullable()->comment('NULL if first price ever set');
            $table->decimal('new_price', 10, 6);
            $table->string('currency', 3)->default('GBP');
            $table->timestamps();

            $table->foreign('pricing_event_id')->references('id')->on('pricing_events')->onDelete('cascade');
            $table->foreign('service_catalogue_id')->references('id')->on('service_catalogue');

            $table->index('pricing_event_id');
            $table->index('service_catalogue_id');
        });

        // UUID triggers
        foreach (['pricing_events', 'pricing_event_items'] as $tbl) {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION generate_uuid_{$tbl}()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;

                CREATE TRIGGER before_insert_{$tbl}_uuid
                BEFORE INSERT ON {$tbl}
                FOR EACH ROW EXECUTE FUNCTION generate_uuid_{$tbl}();
            ");
        }
    }

    public function down(): void
    {
        foreach (['pricing_event_items', 'pricing_events'] as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
            Schema::dropIfExists($tbl);
        }
        DB::statement("DROP TYPE IF EXISTS pricing_event_status CASCADE");
    }
};

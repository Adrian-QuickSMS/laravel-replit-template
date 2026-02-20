<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE billable_product_type AS ENUM (
            'sms', 'rcs_basic', 'rcs_single', 'ai_query',
            'virtual_number_monthly', 'shortcode_monthly',
            'inbound_sms', 'support'
        )");

        DB::statement("CREATE TYPE price_source AS ENUM ('hubspot', 'admin_override')");

        // Fixed tier prices (Starter / Enterprise)
        Schema::create('product_tier_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('country_iso', 2)->nullable()->comment('NULL = default for unlisted countries');
            $table->decimal('unit_price', 10, 6);
            $table->string('currency', 3)->default('GBP');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE product_tier_prices ADD COLUMN product_tier product_tier NOT NULL");
        DB::statement("ALTER TABLE product_tier_prices ADD COLUMN product_type billable_product_type NOT NULL");
        DB::statement("CREATE INDEX idx_tier_prices_lookup ON product_tier_prices (product_tier, product_type, country_iso, active)");
        DB::statement("CREATE INDEX idx_tier_prices_valid ON product_tier_prices (active, valid_from, valid_to)");

        // Per-customer bespoke pricing
        Schema::create('customer_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('country_iso', 2)->nullable();
            $table->decimal('unit_price', 10, 6);
            $table->string('currency', 3)->default('GBP');
            $table->string('hubspot_deal_line_item_id')->nullable();
            $table->uuid('set_by')->nullable()->comment('Admin who set override');
            $table->timestamp('set_at')->nullable();
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('version')->default(1);
            $table->uuid('previous_version_id')->nullable();
            $table->string('change_reason')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('previous_version_id')->references('id')->on('customer_prices');
        });
        DB::statement("ALTER TABLE customer_prices ADD COLUMN product_type billable_product_type NOT NULL");
        DB::statement("ALTER TABLE customer_prices ADD COLUMN source price_source NOT NULL DEFAULT 'admin_override'");
        DB::statement("CREATE INDEX idx_customer_prices_lookup ON customer_prices (account_id, product_type, country_iso, active)");
        DB::statement("CREATE INDEX idx_customer_prices_source ON customer_prices (account_id, source, active)");

        // HubSpot ↔ Platform conflict detection log
        Schema::create('pricing_sync_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('field_path', 100)->comment('e.g., sms.GB.unit_price');
            $table->string('old_value')->nullable();
            $table->string('new_value');
            $table->string('source', 20)->comment('hubspot or admin');
            $table->timestamp('hubspot_timestamp')->nullable();
            $table->timestamp('admin_timestamp')->nullable();
            $table->boolean('conflict_detected')->default(false);
            $table->boolean('conflict_resolved')->default(false);
            $table->uuid('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution', 30)->nullable()->comment('accept_hubspot, accept_admin, custom');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index(['account_id', 'conflict_detected', 'conflict_resolved']);
            $table->index('conflict_detected');
        });

        foreach (['product_tier_prices', 'customer_prices', 'pricing_sync_log'] as $tbl) {
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
        foreach (['pricing_sync_log', 'customer_prices', 'product_tier_prices'] as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
            Schema::dropIfExists($tbl);
        }
        DB::statement("DROP TYPE IF EXISTS price_source CASCADE");
        DB::statement("DROP TYPE IF EXISTS billable_product_type CASCADE");
    }
};

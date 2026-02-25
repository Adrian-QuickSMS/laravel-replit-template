<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // 1. Add new product types to billable_product_type ENUM
        // =====================================================
        // Note: PostgreSQL ENUMs are extended via ALTER TYPE
        DB::statement("DO $$
        BEGIN
            IF NOT EXISTS (SELECT 1 FROM pg_enum WHERE enumlabel = 'virtual_number_setup' AND enumtypid = (SELECT oid FROM pg_type WHERE typname = 'billable_product_type')) THEN
                ALTER TYPE billable_product_type ADD VALUE IF NOT EXISTS 'virtual_number_setup';
            END IF;
            IF NOT EXISTS (SELECT 1 FROM pg_enum WHERE enumlabel = 'shortcode_keyword_setup' AND enumtypid = (SELECT oid FROM pg_type WHERE typname = 'billable_product_type')) THEN
                ALTER TYPE billable_product_type ADD VALUE IF NOT EXISTS 'shortcode_keyword_setup';
            END IF;
        END $$;");

        // =====================================================
        // 2. VMN Pool — admin-seeded available numbers
        // =====================================================
        Schema::create('vmn_pool', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number', 20)->unique()->comment('E.164 format');
            $table->string('country_iso', 2);
            $table->string('number_type', 20)->default('mobile')->comment('mobile, landline, toll_free');
            $table->string('capabilities', 20)->default('sms')->comment('sms, voice, sms_voice');
            $table->string('provider', 50)->nullable()->comment('Carrier/provider name');
            $table->string('provider_reference', 100)->nullable()->comment('Provider-side ID for this number');
            $table->decimal('monthly_cost_override', 10, 4)->nullable()->comment('Override tier price');
            $table->decimal('setup_cost_override', 10, 4)->nullable()->comment('Override tier setup fee');
            $table->boolean('is_available')->default(true);
            $table->uuid('reserved_by_account_id')->nullable()->comment('Temporary lock during purchase flow');
            $table->timestamp('reserved_until')->nullable();
            $table->uuid('added_by')->nullable()->comment('Admin user who seeded this number');
            $table->timestamps();

            $table->index(['country_iso', 'is_available']);
            $table->index(['is_available', 'number_type']);
        });

        // =====================================================
        // 3. Purchased Numbers — tenant-owned numbers
        // =====================================================
        Schema::create('purchased_numbers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('vmn_pool_id')->nullable()->comment('NULL for shortcodes');
            $table->string('number', 20)->comment('E.164 format');
            $table->string('number_type', 30)->comment('vmn, shared_shortcode, dedicated_shortcode');
            $table->string('country_iso', 2);
            $table->string('friendly_name', 255)->nullable();
            $table->string('status', 20)->default('active')->comment('active, suspended, released');

            // Billing
            $table->decimal('setup_fee', 10, 4)->default(0);
            $table->decimal('monthly_fee', 10, 4)->default(0);
            $table->string('currency', 3)->default('GBP');

            // Timestamps
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            // Configuration (JSONB for flexible config)
            $table->jsonb('configuration')->nullable()->comment('Forwarding URL, email, auth headers, retry policy');

            // Sender ID linkage
            $table->uuid('sender_id_id')->nullable()->comment('Auto-created SenderId record');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index(['account_id', 'status']);
            $table->index(['account_id', 'number_type']);
            $table->index('number');
            $table->index('status');
        });

        // =====================================================
        // 4. Shortcode Keywords — keywords on shared short codes
        // =====================================================
        Schema::create('shortcode_keywords', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('purchased_number_id')->comment('Links to the shared shortcode in purchased_numbers');
            $table->string('keyword', 30)->comment('The keyword (case-insensitive)');
            $table->string('status', 20)->default('active')->comment('active, suspended, released');

            // Billing
            $table->decimal('setup_fee', 10, 4)->default(0);
            $table->decimal('monthly_fee', 10, 4)->default(0);
            $table->string('currency', 3)->default('GBP');

            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('purchased_number_id')->references('id')->on('purchased_numbers');
            $table->unique(['purchased_number_id', 'keyword']);
            $table->index(['account_id', 'status']);
        });

        // =====================================================
        // 5. Number Assignments — polymorphic (same pattern as SenderIdAssignment)
        // =====================================================
        Schema::create('number_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('purchased_number_id');
            $table->string('assignable_type', 100)->comment('App\\Models\\SubAccount or App\\Models\\User');
            $table->uuid('assignable_id');
            $table->uuid('assigned_by')->nullable();
            $table->timestamps();

            $table->foreign('purchased_number_id')->references('id')->on('purchased_numbers');
            $table->index(['assignable_type', 'assignable_id']);
            $table->index('purchased_number_id');
        });

        // =====================================================
        // 6. Number Auto-Reply Rules — keyword-based auto-reply
        // =====================================================
        Schema::create('number_auto_reply_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('purchased_number_id');
            $table->string('keyword', 100)->comment('Keyword to match (case-insensitive), or * for catch-all');
            $table->text('reply_content')->comment('Auto-reply message content');
            $table->string('match_type', 20)->default('exact')->comment('exact, starts_with, contains');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0)->comment('Higher priority rules match first');
            $table->boolean('charge_for_reply')->default(true)->comment('Whether to bill for the outbound reply');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('purchased_number_id')->references('id')->on('purchased_numbers');
            $table->index(['purchased_number_id', 'is_active', 'priority']);
        });

        // =====================================================
        // UUID auto-generation triggers (consistent with existing pattern)
        // =====================================================
        $tables = [
            'vmn_pool', 'purchased_numbers', 'shortcode_keywords',
            'number_assignments', 'number_auto_reply_rules',
        ];
        foreach ($tables as $tbl) {
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

        // =====================================================
        // Tenant RLS policies for purchased_numbers, shortcode_keywords,
        // number_assignments, number_auto_reply_rules
        // =====================================================
        $tenantTables = ['purchased_numbers', 'shortcode_keywords', 'number_auto_reply_rules'];
        foreach ($tenantTables as $tbl) {
            DB::unprepared("
                ALTER TABLE {$tbl} ENABLE ROW LEVEL SECURITY;

                CREATE POLICY {$tbl}_tenant_isolation ON {$tbl}
                    USING (account_id::text = current_setting('app.current_tenant_id', true));
            ");
        }
    }

    public function down(): void
    {
        $tenantTables = ['purchased_numbers', 'shortcode_keywords', 'number_auto_reply_rules'];
        foreach ($tenantTables as $tbl) {
            DB::unprepared("DROP POLICY IF EXISTS {$tbl}_tenant_isolation ON {$tbl}");
            DB::unprepared("ALTER TABLE {$tbl} DISABLE ROW LEVEL SECURITY");
        }

        $tables = [
            'number_auto_reply_rules', 'number_assignments', 'shortcode_keywords',
            'purchased_numbers', 'vmn_pool',
        ];
        foreach ($tables as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
            Schema::dropIfExists($tbl);
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Opt-Out Records
 *
 * DATA CLASSIFICATION: Confidential - Compliance/Regulatory
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * PURPOSE:
 * - Individual opt-out entries keyed by mobile_number (NOT contact_id)
 * - Persists even if the contact record is deleted (compliance requirement)
 * - source tracks how the opt-out occurred: sms_reply (STOP), url_click, api, manual
 * - campaign_ref links to the campaign that triggered the opt-out (nullable)
 * - Pre-send checks query this table by account_id + mobile_number
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE opt_out_source AS ENUM ('sms_reply', 'url_click', 'api', 'manual')");

        Schema::create('opt_out_records', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('account_id')->comment('FK to accounts.id — for fast pre-send lookups');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->uuid('opt_out_list_id')->comment('FK to opt_out_lists.id');
            $table->foreign('opt_out_list_id')->references('id')->on('opt_out_lists')->onDelete('cascade');

            // Keyed by mobile_number, NOT contact_id — persists after contact deletion
            $table->string('mobile_number', 20)->comment('E.164 format — primary suppression key');

            // Campaign attribution (nullable — manual/API opt-outs may not have a campaign)
            $table->string('campaign_ref')->nullable()->comment('Campaign ID/reference that triggered the opt-out');

            $table->timestamp('created_at')->useCurrent();

            // One mobile number per opt-out list
            $table->unique(['opt_out_list_id', 'mobile_number']);

            // Fast pre-send lookup: is this number opted out for this account?
            $table->index(['account_id', 'mobile_number']);
            $table->index('opt_out_list_id');
        });

        // Add ENUM column
        DB::statement("ALTER TABLE opt_out_records ADD COLUMN source opt_out_source DEFAULT 'manual'");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_opt_out_records()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_opt_out_records_uuid
            BEFORE INSERT ON opt_out_records
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_opt_out_records();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE opt_out_records ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE opt_out_records FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY opt_out_records_tenant_isolation ON opt_out_records
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY opt_out_records_postgres_bypass ON opt_out_records
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS opt_out_records_postgres_bypass ON opt_out_records");
        DB::unprepared("DROP POLICY IF EXISTS opt_out_records_tenant_isolation ON opt_out_records");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_opt_out_records_uuid ON opt_out_records");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_opt_out_records()");

        Schema::dropIfExists('opt_out_records');

        DB::statement("DROP TYPE IF EXISTS opt_out_source CASCADE");
    }
};

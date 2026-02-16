<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Opt-Out Lists (Suppression Lists)
 *
 * DATA CLASSIFICATION: Confidential - Compliance/Regulatory
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * PURPOSE:
 * - Suppression lists that block message sends
 * - One master opt-out list per account (is_master = true), enforced by partial unique index
 * - Additional campaign/channel-specific opt-out lists
 * - count is denormalised for UI performance
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opt_out_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('account_id')->comment('FK to accounts.id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_master')->default(false)->comment('One master list per account — global suppression');

            // Denormalised count
            $table->integer('count')->default(0);

            $table->timestamps();

            $table->unique(['account_id', 'name']);
            $table->index('account_id');
        });

        // Partial unique index: only one is_master=true per account
        DB::statement("
            CREATE UNIQUE INDEX idx_opt_out_lists_one_master_per_account
            ON opt_out_lists (account_id)
            WHERE is_master = true
        ");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_opt_out_lists()
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
            CREATE TRIGGER before_insert_opt_out_lists_uuid
            BEFORE INSERT ON opt_out_lists
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_opt_out_lists();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE opt_out_lists ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE opt_out_lists FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY opt_out_lists_tenant_isolation ON opt_out_lists
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY opt_out_lists_postgres_bypass ON opt_out_lists
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS opt_out_lists_postgres_bypass ON opt_out_lists");
        DB::unprepared("DROP POLICY IF EXISTS opt_out_lists_tenant_isolation ON opt_out_lists");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_opt_out_lists_uuid ON opt_out_lists");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_opt_out_lists()");

        Schema::dropIfExists('opt_out_lists');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Sub-Accounts table - Account hierarchy: Account > Sub-Account > User
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->comment('FK to accounts.id - parent tenant');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable()->comment('FK to users.id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index('account_id');
            $table->index('is_active');
            $table->index(['account_id', 'is_active']);
            $table->unique(['account_id', 'name']);
        });

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_sub_accounts()
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
            CREATE TRIGGER before_insert_sub_accounts_uuid
            BEFORE INSERT ON sub_accounts
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_sub_accounts();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE sub_accounts ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE sub_accounts FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY sub_accounts_isolation ON sub_accounts
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Service role bypass for admin operations
        DB::unprepared("
            CREATE POLICY sub_accounts_service_access ON sub_accounts
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS sub_accounts_service_access ON sub_accounts");
        DB::unprepared("DROP POLICY IF EXISTS sub_accounts_isolation ON sub_accounts");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_sub_accounts_uuid ON sub_accounts");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_sub_accounts()");
        Schema::dropIfExists('sub_accounts');
    }
};

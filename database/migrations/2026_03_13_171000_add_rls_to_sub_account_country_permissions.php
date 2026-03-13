<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable RLS
        DB::unprepared('ALTER TABLE sub_account_country_permissions ENABLE ROW LEVEL SECURITY');
        DB::unprepared('ALTER TABLE sub_account_country_permissions FORCE ROW LEVEL SECURITY');

        // Tenant isolation policy: join through sub_accounts to get account_id,
        // then check against app.current_tenant_id
        DB::unprepared("
            CREATE POLICY sub_account_country_permissions_isolation
            ON sub_account_country_permissions
            FOR ALL
            USING (
                sub_account_id IN (
                    SELECT id FROM sub_accounts
                    WHERE account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                )
            )
            WITH CHECK (
                sub_account_id IN (
                    SELECT id FROM sub_accounts
                    WHERE account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
                )
            );
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP POLICY IF EXISTS sub_account_country_permissions_isolation ON sub_account_country_permissions');
        DB::unprepared('ALTER TABLE sub_account_country_permissions DISABLE ROW LEVEL SECURITY');
    }
};

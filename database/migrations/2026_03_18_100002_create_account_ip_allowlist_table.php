<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Account IP Allowlist — restricts portal login to specific IPs/CIDRs per account.
 *
 * DATA CLASSIFICATION: Internal - Account Security Configuration
 * SIDE: GREEN (customer-configurable)
 * TENANT ISOLATION: tenant_id + RLS
 *
 * Limit: 50 entries per account (enforced in application layer)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('account_ip_allowlist')) {
            return;
        }

        Schema::create('account_ip_allowlist', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('tenant_id')->comment('FK to accounts.id — RLS key');
            $table->string('ip_address', 45)->comment('IPv4/IPv6 address or CIDR range (e.g. 192.168.1.0/24)');
            $table->string('label', 100)->nullable()->comment('Friendly name for this IP entry');
            $table->uuid('created_by')->comment('User ID who added this entry');
            $table->string('status', 20)->default('active')->comment('active or disabled');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->index(['tenant_id', 'status']);
        });

        // Enable RLS
        DB::unprepared("ALTER TABLE account_ip_allowlist ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE account_ip_allowlist FORCE ROW LEVEL SECURITY");

        // Tenant isolation policy
        DB::unprepared("
            CREATE POLICY account_ip_allowlist_isolation ON account_ip_allowlist
            FOR ALL
            USING (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Privileged roles bypass
        DB::unprepared("
            CREATE POLICY account_ip_allowlist_service_access ON account_ip_allowlist
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");

        // Postgres superuser bypass
        DB::unprepared("
            CREATE POLICY account_ip_allowlist_postgres_bypass ON account_ip_allowlist
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS account_ip_allowlist_postgres_bypass ON account_ip_allowlist");
        DB::unprepared("DROP POLICY IF EXISTS account_ip_allowlist_service_access ON account_ip_allowlist");
        DB::unprepared("DROP POLICY IF EXISTS account_ip_allowlist_isolation ON account_ip_allowlist");
        Schema::dropIfExists('account_ip_allowlist');
    }
};

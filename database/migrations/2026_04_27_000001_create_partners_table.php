<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Partners (Reseller / White-label) table — Phase 0, PR-1
 *
 * DATA CLASSIFICATION: Internal — Reseller Configuration
 * SIDE: Cross-zone (RED admin owns the row, GREEN partner portal reads it in
 *       a future PR via app.current_partner_id session variable).
 * TENANT ISOLATION: This is a NEW tenant root layer for the partner portal.
 *                   RLS uses app.current_partner_id (set by SetPartnerContext
 *                   middleware in a later PR). Until that middleware exists,
 *                   portal_rw queries return zero rows (fail-closed).
 *
 * SCOPE OF THIS PR (Phase 0, PR-1):
 *   - Additive only. Creates `partners` table.
 *   - No code references this table yet (no controllers, no auth path).
 *   - RLS policies are in place ready for SetPartnerContext middleware.
 *   - Service-role policies (svc_red / ops_admin) are added defensively —
 *     skipped if the role does not exist in this environment.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('partners')) {
            return;
        }

        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('legal_name');
            $table->string('trading_name')->nullable();

            $table->string('status', 20)->default('active');
            $table->string('contract_type', 40)->default('standard');
            $table->char('currency', 3)->default('GBP');

            // The partner's own QuickSMS account (their internal billing/admin
            // identity inside QuickSMS). Optional — partners may exist before
            // they have a billing account.
            $table->uuid('owner_account_id')->nullable();

            // Audit fields (match accounts table convention)
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('owner_account_id');

            $table->foreign('owner_account_id')
                ->references('id')->on('accounts')
                ->nullOnDelete();
        });

        // Status check constraint — keeps the value space narrow without
        // creating a Postgres ENUM type (those are painful to alter).
        DB::statement("
            ALTER TABLE partners
            ADD CONSTRAINT chk_partners_status
            CHECK (status IN ('active', 'suspended', 'closed'))
        ");

        // UUID auto-generation trigger (mirrors accounts / sub_accounts pattern)
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_partners()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_partners_uuid ON partners");
        DB::unprepared("
            CREATE TRIGGER before_insert_partners_uuid
            BEFORE INSERT ON partners
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_partners();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE partners ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE partners FORCE ROW LEVEL SECURITY");

        // Self-access: a partner_user session sees only their own partner row.
        // app.current_partner_id is set by SetPartnerContext middleware (later PR).
        // Until then, this policy matches zero rows for portal_rw — correct.
        DB::unprepared("
            CREATE POLICY partners_self_access ON partners
            FOR ALL
            USING (
                id = NULLIF(current_setting('app.current_partner_id', true), '')::uuid
            )
            WITH CHECK (
                id = NULLIF(current_setting('app.current_partner_id', true), '')::uuid
            );
        ");

        // Service / admin roles bypass — defensive, skip if role missing.
        $this->safePolicy("
            CREATE POLICY partners_service_access ON partners
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");

        // Application connects as postgres in dev; production may use a
        // dedicated app role. Bypass policy keeps app code working.
        $this->safePolicy("
            CREATE POLICY partners_postgres_bypass ON partners
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS partners_postgres_bypass ON partners");
        DB::unprepared("DROP POLICY IF EXISTS partners_service_access ON partners");
        DB::unprepared("DROP POLICY IF EXISTS partners_self_access ON partners");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_partners_uuid ON partners");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_partners()");

        Schema::dropIfExists('partners');
    }

    /**
     * Run a CREATE POLICY statement, swallowing 42704 (undefined role) so
     * environments without svc_red/ops_admin still migrate cleanly.
     * Mirrors safeGrant() in 2026_03_18_100004_grant_portal_rw_security_tables.
     */
    private function safePolicy(string $sql): void
    {
        try {
            DB::unprepared($sql);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), '42704')) {
                return;
            }
            throw $e;
        }
    }
};

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
 * LIFECYCLE: status ('active' | 'suspended' | 'closed') is the only deletion
 *            mechanism. No SoftDeletes — keeping a single lifecycle axis avoids
 *            the orphan-relation problem where soft-deleted partners leave
 *            child accounts.partner_id pointing at hidden rows.
 *
 * IDEMPOTENCY: every step is independently re-runnable so a partial failure
 *              followed by re-run does not leave the table without RLS policies.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: table.
        if (! Schema::hasTable('partners')) {
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

                $table->index('status');
                $table->index('owner_account_id');

                $table->foreign('owner_account_id')
                    ->references('id')->on('accounts')
                    ->nullOnDelete();
            });
        }

        // Step 2: status check constraint. Drop-then-add for idempotency.
        DB::statement("ALTER TABLE partners DROP CONSTRAINT IF EXISTS chk_partners_status");
        DB::statement("
            ALTER TABLE partners
            ADD CONSTRAINT chk_partners_status
            CHECK (status IN ('active', 'suspended', 'closed'))
        ");

        // Step 3: UUID auto-generation function and trigger.
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

        // Step 4: Row Level Security. ENABLE/FORCE are idempotent in Postgres
        // (re-running on an already-enabled table is a no-op).
        DB::unprepared("ALTER TABLE partners ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE partners FORCE ROW LEVEL SECURITY");

        // Step 5: policies. Drop-then-create each so partial-failure recovery
        // and re-runs are safe. CREATE POLICY has no IF NOT EXISTS form.

        DB::unprepared("DROP POLICY IF EXISTS partners_self_access ON partners");
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

        // Service / admin roles bypass — defensive: skip CREATE if role missing.
        // DROP IF EXISTS is safe regardless of role existence.
        DB::unprepared("DROP POLICY IF EXISTS partners_service_access ON partners");
        $this->safePolicy("
            CREATE POLICY partners_service_access ON partners
            FOR ALL
            TO svc_red, ops_admin
            USING (true)
            WITH CHECK (true);
        ");

        DB::unprepared("DROP POLICY IF EXISTS partners_postgres_bypass ON partners");
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
        DB::statement("ALTER TABLE IF EXISTS partners DROP CONSTRAINT IF EXISTS chk_partners_status");

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

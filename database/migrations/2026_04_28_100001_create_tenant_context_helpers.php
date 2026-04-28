<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tenant-context helper functions — Phase 0, PR-2
 *
 * Adds three Postgres SQL functions that PR-3 will use to express the
 * partner→account hierarchy in RLS policies. NO existing policy is modified
 * here; this PR only defines the helpers.
 *
 *   current_tenant_id()           — typed wrapper around app.current_tenant_id GUC
 *   current_partner_id()          — typed wrapper around app.current_partner_id GUC
 *   account_in_scope(uuid)        — TRUE if the given account is the current
 *                                   tenant OR (partner context is set AND
 *                                   the account belongs to that partner)
 *
 * SECURITY MODEL
 * --------------
 * account_in_scope() is SECURITY DEFINER, owned by postgres (superuser).
 *
 * Why DEFINER: the function reads accounts.partner_id to resolve hierarchy.
 * The accounts table has its own RLS that only matches `id = current_tenant_id()`.
 * If the function ran SECURITY INVOKER, a partner_user calling it via a
 * future RLS policy would hit accounts RLS, find nothing, and the hierarchy
 * check would fail-closed for legitimate partner→child lookups.
 *
 * Why postgres-owned: superuser bypasses RLS unconditionally. Alternative
 * owners (svc_red) work but introduce a hidden dependency on the
 * accounts_service_access policy continuing to exist. Postgres ownership is
 * the most stable choice for a load-bearing helper.
 *
 * Defences
 * --------
 *   - SET search_path = pg_catalog, pg_temp inside the function prevents
 *     schema-injection where a user-writable schema shadows `accounts`.
 *   - REVOKE ALL FROM PUBLIC then explicit GRANT EXECUTE to the four
 *     project roles. Default PUBLIC EXECUTE on functions is a footgun.
 *   - STABLE volatility: same input → same output within a statement;
 *     between statements the session GUCs may change.
 *   - Typed UUID parameter and BOOLEAN return — no string-injection surface.
 *
 * Idempotency
 * -----------
 *   - CREATE OR REPLACE FUNCTION is naturally idempotent.
 *   - REVOKE / GRANT statements are idempotent and wrapped defensively for
 *     environments where roles may not exist (matches safeGrant() pattern
 *     in 2026_03_18_100004_grant_portal_rw_security_tables.php).
 */
return new class extends Migration
{
    public function up(): void
    {
        // -- 1. current_tenant_id() ------------------------------------------
        DB::unprepared("
            CREATE OR REPLACE FUNCTION current_tenant_id()
            RETURNS UUID
            LANGUAGE sql
            STABLE
            AS \$\$
                SELECT NULLIF(current_setting('app.current_tenant_id', true), '')::uuid;
            \$\$;
        ");

        // -- 2. current_partner_id() -----------------------------------------
        DB::unprepared("
            CREATE OR REPLACE FUNCTION current_partner_id()
            RETURNS UUID
            LANGUAGE sql
            STABLE
            AS \$\$
                SELECT NULLIF(current_setting('app.current_partner_id', true), '')::uuid;
            \$\$;
        ");

        // -- 3. account_in_scope(uuid) ---------------------------------------
        // SECURITY DEFINER + locked search_path. See header for rationale.
        DB::unprepared("
            CREATE OR REPLACE FUNCTION account_in_scope(target_account_id UUID)
            RETURNS BOOLEAN
            LANGUAGE sql
            STABLE
            SECURITY DEFINER
            SET search_path = pg_catalog, pg_temp
            AS \$\$
                SELECT
                    target_account_id = current_tenant_id()
                    OR (
                        current_partner_id() IS NOT NULL
                        AND EXISTS (
                            SELECT 1 FROM public.accounts
                            WHERE id = target_account_id
                              AND partner_id = current_partner_id()
                        )
                    );
            \$\$;
        ");

        // -- 4. Lock down EXECUTE privileges ---------------------------------
        // PostgreSQL grants EXECUTE to PUBLIC by default on new functions.
        // Revoke that and grant explicitly to the four project roles.
        DB::unprepared("REVOKE ALL ON FUNCTION current_tenant_id() FROM PUBLIC");
        DB::unprepared("REVOKE ALL ON FUNCTION current_partner_id() FROM PUBLIC");
        DB::unprepared("REVOKE ALL ON FUNCTION account_in_scope(UUID) FROM PUBLIC");

        $this->safeGrant("GRANT EXECUTE ON FUNCTION current_tenant_id() TO portal_rw, portal_ro, svc_red, ops_admin");
        $this->safeGrant("GRANT EXECUTE ON FUNCTION current_partner_id() TO portal_rw, portal_ro, svc_red, ops_admin");
        $this->safeGrant("GRANT EXECUTE ON FUNCTION account_in_scope(UUID) TO portal_rw, portal_ro, svc_red, ops_admin");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS account_in_scope(UUID)");
        DB::unprepared("DROP FUNCTION IF EXISTS current_partner_id()");
        DB::unprepared("DROP FUNCTION IF EXISTS current_tenant_id()");
    }

    /**
     * Run a GRANT, swallowing 42704 (undefined role) so environments where
     * portal_rw / svc_red / ops_admin are not provisioned still migrate.
     * Mirrors safeGrant() in 2026_03_18_100004_grant_portal_rw_security_tables.
     */
    private function safeGrant(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), '42704')) {
                return;
            }
            throw $e;
        }
    }
};

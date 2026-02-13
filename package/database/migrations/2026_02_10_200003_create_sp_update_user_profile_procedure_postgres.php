<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Update User Profile (PostgreSQL Version)
     *
     * FUNCTION: sp_update_user_profile
     * PURPOSE: Safely update user profile information
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID parameters (no hex conversion needed)
     * - Returns TABLE for Laravel compatibility
     * - SECURITY DEFINER for consistent execution context
     * - Exception handling with PL/pgSQL
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Can only update own profile (tenant_id must match)
     * - Cannot change: email, password, role, status
     * - Cannot update password (use separate password change flow)
     * - Validates tenant ownership before update
     *
     * PARAMETERS:
     * - p_user_id: User UUID (TEXT, converted to UUID)
     * - p_tenant_id: Tenant UUID for validation (TEXT, converted to UUID)
     * - p_first_name: New first name
     * - p_last_name: New last name
     *
     * RETURNS: TABLE with status and message
     */
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_update_user_profile(TEXT, TEXT, TEXT, TEXT)");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_update_user_profile(
                p_user_id TEXT,
                p_tenant_id TEXT,
                p_first_name TEXT,
                p_last_name TEXT
            )
            RETURNS TABLE(
                status TEXT,
                message TEXT
            )
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = public
            AS \$\$
            DECLARE
                v_user_id UUID;
                v_tenant_id UUID;
                v_actual_tenant_id UUID;
            BEGIN
                -- Convert text UUIDs to UUID type
                v_user_id := p_user_id::UUID;
                v_tenant_id := p_tenant_id::UUID;

                -- Verify user belongs to tenant
                SELECT tenant_id INTO v_actual_tenant_id
                FROM users
                WHERE id = v_user_id
                LIMIT 1;

                IF v_actual_tenant_id IS NULL THEN
                    RAISE EXCEPTION 'User not found';
                END IF;

                IF v_actual_tenant_id != v_tenant_id THEN
                    RAISE EXCEPTION 'Unauthorized: Tenant mismatch';
                END IF;

                -- Update user profile
                UPDATE users
                SET
                    first_name = p_first_name,
                    last_name = p_last_name,
                    updated_at = NOW()
                WHERE id = v_user_id;

                RETURN QUERY
                SELECT
                    'success'::TEXT,
                    'Profile updated successfully'::TEXT;

            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION 'Profile update failed: %', SQLERRM;
            END;
            \$\$;
        ");

        -- Grant EXECUTE permission to portal_rw role
        DB::unprepared("GRANT EXECUTE ON FUNCTION sp_update_user_profile TO portal_rw");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_update_user_profile(TEXT, TEXT, TEXT, TEXT)");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Update User Profile
     *
     * PROCEDURE: sp_update_user_profile
     * PURPOSE: Safely update user profile information
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Can only update own profile (tenant_id must match)
     * - Cannot change: email, password, role, status
     * - Cannot update password (use sp_change_password)
     * - Logs profile changes
     *
     * PARAMETERS:
     * - p_user_id: User UUID
     * - p_tenant_id: Tenant UUID (for validation)
     * - p_first_name: New first name
     * - p_last_name: New last name
     *
     * RETURNS: Success or error
     */
    public function up(): void
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS sp_update_user_profile(VARCHAR, VARCHAR, VARCHAR, VARCHAR);
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_update_user_profile(
                p_user_id_hex VARCHAR(36),
                p_tenant_id_hex VARCHAR(36),
                p_first_name VARCHAR(100),
                p_last_name VARCHAR(100)
            ) RETURNS TABLE(status TEXT, message TEXT)
            LANGUAGE plpgsql AS \$\$
            DECLARE
                v_user_id UUID;
                v_tenant_id UUID;
                v_actual_tenant_id UUID;
            BEGIN
                -- Convert hex UUIDs to UUID type
                v_user_id := p_user_id_hex::UUID;
                v_tenant_id := p_tenant_id_hex::UUID;

                -- Verify user belongs to tenant
                SELECT u.tenant_id INTO v_actual_tenant_id
                FROM users u
                WHERE u.id = v_user_id
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

                RETURN QUERY SELECT
                    'success'::TEXT,
                    'Profile updated successfully'::TEXT;

            END;
            \$\$
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_update_user_profile(VARCHAR, VARCHAR, VARCHAR, VARCHAR)");
    }
};

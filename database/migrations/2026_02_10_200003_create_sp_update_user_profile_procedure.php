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
            DROP PROCEDURE IF EXISTS sp_update_user_profile;
        ");

        DB::unprepared("
            CREATE PROCEDURE sp_update_user_profile(
                IN p_user_id_hex VARCHAR(36),
                IN p_tenant_id_hex VARCHAR(36),
                IN p_first_name VARCHAR(100),
                IN p_last_name VARCHAR(100)
            )
            BEGIN
                DECLARE v_user_id BINARY(16);
                DECLARE v_tenant_id BINARY(16);
                DECLARE v_actual_tenant_id BINARY(16);

                -- Convert hex UUIDs to binary
                SET v_user_id = UNHEX(REPLACE(p_user_id_hex, '-', ''));
                SET v_tenant_id = UNHEX(REPLACE(p_tenant_id_hex, '-', ''));

                -- Verify user belongs to tenant
                SELECT tenant_id INTO v_actual_tenant_id
                FROM users
                WHERE id = v_user_id
                LIMIT 1;

                IF v_actual_tenant_id IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'User not found';
                END IF;

                IF v_actual_tenant_id != v_tenant_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Unauthorized: Tenant mismatch';
                END IF;

                -- Update user profile
                UPDATE users
                SET
                    first_name = p_first_name,
                    last_name = p_last_name,
                    updated_at = NOW()
                WHERE id = v_user_id;

                SELECT
                    'success' as status,
                    'Profile updated successfully' as message;

            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_update_user_profile");
    }
};

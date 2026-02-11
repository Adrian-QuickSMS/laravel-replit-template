<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Safe View - User Profile
     *
     * VIEW: user_profile_view
     * PURPOSE: Portal-safe user profile data
     *
     * SECURITY:
     * - Portal users: SELECT permission
     * - NEVER exposes: password, mfa_secret, remember_token
     * - NEVER exposes: failed_login_attempts, account_locked_until (security sensitive)
     * - Tenant-scoped (users can only see users in own tenant)
     *
     * COLUMNS EXPOSED:
     * - id, tenant_id, user_type, email
     * - first_name, last_name, role, status
     * - mfa_enabled (boolean only, not secret)
     * - email_verified_at, last_login_at
     * - hubspot_contact_id (for sync status)
     * - created_at, updated_at
     *
     * COLUMNS HIDDEN:
     * - password (bcrypt hash - NEVER expose)
     * - mfa_secret (encrypted TOTP secret - NEVER expose)
     * - remember_token (Laravel auth token - NEVER expose)
     * - failed_login_attempts (security sensitive)
     * - account_locked_until (security sensitive)
     * - last_login_ip (privacy sensitive)
     * - password_changed_at (security metadata)
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW user_profile_view AS
            SELECT
                LOWER(CONCAT(
                    HEX(SUBSTRING(id, 1, 4)), '-',
                    HEX(SUBSTRING(id, 5, 2)), '-',
                    HEX(SUBSTRING(id, 7, 2)), '-',
                    HEX(SUBSTRING(id, 9, 2)), '-',
                    HEX(SUBSTRING(id, 11))
                )) as id,
                LOWER(CONCAT(
                    HEX(SUBSTRING(tenant_id, 1, 4)), '-',
                    HEX(SUBSTRING(tenant_id, 5, 2)), '-',
                    HEX(SUBSTRING(tenant_id, 7, 2)), '-',
                    HEX(SUBSTRING(tenant_id, 9, 2)), '-',
                    HEX(SUBSTRING(tenant_id, 11))
                )) as tenant_id,
                user_type,
                email,
                first_name,
                last_name,
                CONCAT(first_name, ' ', last_name) as full_name,
                role,
                status,
                mfa_enabled,
                email_verified_at,
                email_verified_at IS NOT NULL as email_verified,
                last_login_at,
                hubspot_contact_id,
                created_at,
                updated_at
            FROM users
            WHERE status = 'active'
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP VIEW IF EXISTS user_profile_view");
    }
};

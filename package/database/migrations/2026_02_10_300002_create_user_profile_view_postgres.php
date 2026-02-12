<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Safe View - User Profile (PostgreSQL Version)
     *
     * VIEW: user_profile_view
     * PURPOSE: Portal-safe user profile data
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID casting (no HEX conversion needed)
     * - ENUM types cast to TEXT for JSON serialization
     * - Cleaner, more readable SQL
     *
     * SECURITY:
     * - Portal users: SELECT permission
     * - NEVER exposes: password, mfa_secret, remember_token
     * - NEVER exposes: failed_login_attempts, account_locked_until (security sensitive)
     * - Tenant-scoped via RLS on underlying users table
     *
     * COLUMNS EXPOSED:
     * - id, tenant_id, user_type, email
     * - first_name, last_name, full_name, role, status
     * - mfa_enabled (boolean only, not secret)
     * - email_verified_at, email_verified (boolean)
     * - last_login_at
     * - hubspot_contact_id (for sync status)
     * - created_at, updated_at
     *
     * COLUMNS HIDDEN:
     * - password (Argon2id hash - NEVER expose)
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
                id::text as id,
                tenant_id::text as tenant_id,
                user_type::text as user_type,
                email,
                first_name,
                last_name,
                CONCAT(first_name, ' ', last_name) as full_name,
                role::text as role,
                status::text as status,
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

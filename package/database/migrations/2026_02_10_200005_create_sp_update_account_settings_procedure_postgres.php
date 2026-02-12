<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Update Account Settings (PostgreSQL Version)
     *
     * FUNCTION: sp_update_account_settings
     * PURPOSE: Update account-level settings
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID handling
     * - JSONB for notification_email_addresses and webhook_urls
     * - Returns TABLE for Laravel compatibility
     * - SECURITY DEFINER for consistent execution
     * - COALESCE for optional parameter updates
     * - ENUM type checking for user role
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Can only update own account settings
     * - Owners and admins only
     * - Validates user role before update
     * - Validates tenant ownership
     *
     * PARAMETERS:
     * - p_user_id: User UUID (must be owner/admin)
     * - p_account_id: Account UUID
     * - p_notify_low_balance: Enable low balance notifications (BOOLEAN, optional)
     * - p_low_balance_threshold: Low balance threshold amount (NUMERIC, optional)
     * - p_webhook_urls: JSONB array of webhook URLs (optional)
     * - p_timezone: Timezone string (TEXT, optional)
     * - p_currency: Currency code (TEXT, optional)
     * - p_session_timeout_minutes: Session timeout in minutes (INT, optional)
     * - p_require_mfa_for_api: Require MFA for API access (BOOLEAN, optional)
     *
     * RETURNS: TABLE with status and message
     */
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_update_account_settings(TEXT, TEXT, BOOLEAN, NUMERIC, JSONB, TEXT, TEXT, INT, BOOLEAN)");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_update_account_settings(
                p_user_id TEXT,
                p_account_id TEXT,
                p_notify_low_balance BOOLEAN DEFAULT NULL,
                p_low_balance_threshold NUMERIC DEFAULT NULL,
                p_webhook_urls JSONB DEFAULT NULL,
                p_timezone TEXT DEFAULT NULL,
                p_currency TEXT DEFAULT NULL,
                p_session_timeout_minutes INT DEFAULT NULL,
                p_require_mfa_for_api BOOLEAN DEFAULT NULL
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
                v_account_id UUID;
                v_tenant_id UUID;
                v_user_role user_role;
            BEGIN
                -- Convert text UUIDs to UUID type
                v_user_id := p_user_id::UUID;
                v_account_id := p_account_id::UUID;

                -- Verify user is owner or admin of this account
                SELECT tenant_id, role INTO v_tenant_id, v_user_role
                FROM users
                WHERE id = v_user_id
                LIMIT 1;

                IF v_tenant_id IS NULL THEN
                    RAISE EXCEPTION 'User not found';
                END IF;

                IF v_tenant_id != v_account_id THEN
                    RAISE EXCEPTION 'Unauthorized: Account mismatch';
                END IF;

                IF v_user_role NOT IN ('owner'::user_role, 'admin'::user_role) THEN
                    RAISE EXCEPTION 'Unauthorized: Only owners and admins can update account settings';
                END IF;

                -- Update account settings (only update fields that were provided)
                UPDATE account_settings
                SET
                    notify_low_balance = COALESCE(p_notify_low_balance, notify_low_balance),
                    low_balance_threshold = COALESCE(p_low_balance_threshold, low_balance_threshold),
                    webhook_urls = COALESCE(p_webhook_urls, webhook_urls),
                    timezone = COALESCE(p_timezone, timezone),
                    currency = COALESCE(p_currency, currency),
                    session_timeout_minutes = COALESCE(p_session_timeout_minutes, session_timeout_minutes),
                    require_mfa_for_api = COALESCE(p_require_mfa_for_api, require_mfa_for_api),
                    updated_at = NOW()
                WHERE account_id = v_account_id;

                RETURN QUERY
                SELECT
                    'success'::TEXT,
                    'Account settings updated successfully'::TEXT;

            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION 'Settings update failed: %', SQLERRM;
            END;
            \$\$;
        ");

        -- Grant EXECUTE permission to portal_rw role
        DB::unprepared("GRANT EXECUTE ON FUNCTION sp_update_account_settings TO portal_rw");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_update_account_settings(TEXT, TEXT, BOOLEAN, NUMERIC, JSONB, TEXT, TEXT, INT, BOOLEAN)");
    }
};

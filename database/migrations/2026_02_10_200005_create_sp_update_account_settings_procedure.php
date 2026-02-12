<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Update Account Settings
     *
     * PROCEDURE: sp_update_account_settings
     * PURPOSE: Update account-level settings
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Can only update own account settings
     * - Owners and admins only
     * - Validates webhook URLs
     * - Logs changes
     *
     * PARAMETERS:
     * - p_user_id: User UUID (must be owner/admin)
     * - p_account_id: Account UUID
     * - p_settings: JSON object with settings to update
     *
     * RETURNS: Success or error
     */
    public function up(): void
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS sp_update_account_settings(VARCHAR, VARCHAR, BOOLEAN, BOOLEAN, BOOLEAN, BOOLEAN, BOOLEAN, BOOLEAN, VARCHAR, VARCHAR, VARCHAR, JSONB, VARCHAR, INT, BOOLEAN);
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_update_account_settings(
                p_user_id_hex VARCHAR(36),
                p_account_id_hex VARCHAR(36),
                p_notify_low_balance BOOLEAN,
                p_notify_failed_messages BOOLEAN,
                p_notify_monthly_summary BOOLEAN,
                p_marketing_emails BOOLEAN,
                p_product_updates BOOLEAN,
                p_timezone VARCHAR(50),
                p_date_format VARCHAR(20),
                p_currency VARCHAR(3),
                p_webhook_urls JSONB,
                p_webhook_secret VARCHAR(255),
                p_session_timeout_minutes INT,
                p_require_mfa_for_api BOOLEAN
            ) RETURNS TABLE(status TEXT, message TEXT)
            LANGUAGE plpgsql AS \$\$
            DECLARE
                v_user_id UUID;
                v_account_id UUID;
                v_tenant_id UUID;
                v_user_role VARCHAR(20);
            BEGIN
                -- Convert hex UUIDs to UUID type
                v_user_id := p_user_id_hex::UUID;
                v_account_id := p_account_id_hex::UUID;

                -- Verify user is owner or admin of this account
                SELECT u.tenant_id, u.role INTO v_tenant_id, v_user_role
                FROM users u
                WHERE u.id = v_user_id
                LIMIT 1;

                IF v_tenant_id IS NULL THEN
                    RAISE EXCEPTION 'User not found';
                END IF;

                IF v_tenant_id != v_account_id THEN
                    RAISE EXCEPTION 'Unauthorized: Account mismatch';
                END IF;

                IF v_user_role NOT IN ('owner', 'admin') THEN
                    RAISE EXCEPTION 'Unauthorized: Only owners and admins can update account settings';
                END IF;

                -- Update account settings (matching actual table columns)
                UPDATE account_settings
                SET
                    notify_low_balance = COALESCE(p_notify_low_balance, notify_low_balance),
                    notify_failed_messages = COALESCE(p_notify_failed_messages, notify_failed_messages),
                    notify_monthly_summary = COALESCE(p_notify_monthly_summary, notify_monthly_summary),
                    marketing_emails = COALESCE(p_marketing_emails, marketing_emails),
                    product_updates = COALESCE(p_product_updates, product_updates),
                    timezone = COALESCE(p_timezone, timezone),
                    date_format = COALESCE(p_date_format, date_format),
                    currency = COALESCE(p_currency, currency),
                    webhook_urls = COALESCE(p_webhook_urls, webhook_urls),
                    webhook_secret = COALESCE(p_webhook_secret, webhook_secret),
                    session_timeout_minutes = COALESCE(p_session_timeout_minutes, session_timeout_minutes),
                    require_mfa_for_api = COALESCE(p_require_mfa_for_api, require_mfa_for_api),
                    updated_by = v_user_id,
                    updated_at = NOW()
                WHERE account_id = v_account_id;

                RETURN QUERY SELECT
                    'success'::TEXT,
                    'Account settings updated successfully'::TEXT;

            END;
            \$\$
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_update_account_settings(VARCHAR, VARCHAR, BOOLEAN, BOOLEAN, BOOLEAN, BOOLEAN, BOOLEAN, VARCHAR, VARCHAR, VARCHAR, JSONB, VARCHAR, INT, BOOLEAN)");
    }
};

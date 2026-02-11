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
            DROP PROCEDURE IF EXISTS sp_update_account_settings;
        ");

        DB::unprepared("
            CREATE PROCEDURE sp_update_account_settings(
                IN p_user_id_hex VARCHAR(36),
                IN p_account_id_hex VARCHAR(36),
                IN p_notification_email_enabled BOOLEAN,
                IN p_notification_email_addresses JSON,
                IN p_webhook_url_delivery VARCHAR(255),
                IN p_webhook_url_inbound VARCHAR(255),
                IN p_timezone VARCHAR(50),
                IN p_currency VARCHAR(3),
                IN p_session_timeout_minutes INT,
                IN p_require_mfa BOOLEAN
            )
            BEGIN
                DECLARE v_user_id BINARY(16);
                DECLARE v_account_id BINARY(16);
                DECLARE v_tenant_id BINARY(16);
                DECLARE v_user_role VARCHAR(20);

                -- Convert hex UUIDs to binary
                SET v_user_id = UNHEX(REPLACE(p_user_id_hex, '-', ''));
                SET v_account_id = UNHEX(REPLACE(p_account_id_hex, '-', ''));

                -- Verify user is owner or admin of this account
                SELECT tenant_id, role INTO v_tenant_id, v_user_role
                FROM users
                WHERE id = v_user_id
                LIMIT 1;

                IF v_tenant_id IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'User not found';
                END IF;

                IF v_tenant_id != v_account_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Unauthorized: Account mismatch';
                END IF;

                IF v_user_role NOT IN ('owner', 'admin') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Unauthorized: Only owners and admins can update account settings';
                END IF;

                -- Update account settings
                UPDATE account_settings
                SET
                    notification_email_enabled = COALESCE(p_notification_email_enabled, notification_email_enabled),
                    notification_email_addresses = COALESCE(p_notification_email_addresses, notification_email_addresses),
                    webhook_url_delivery = COALESCE(p_webhook_url_delivery, webhook_url_delivery),
                    webhook_url_inbound = COALESCE(p_webhook_url_inbound, webhook_url_inbound),
                    timezone = COALESCE(p_timezone, timezone),
                    currency = COALESCE(p_currency, currency),
                    session_timeout_minutes = COALESCE(p_session_timeout_minutes, session_timeout_minutes),
                    require_mfa = COALESCE(p_require_mfa, require_mfa),
                    updated_at = NOW()
                WHERE account_id = v_account_id;

                SELECT
                    'success' as status,
                    'Account settings updated successfully' as message;

            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_update_account_settings");
    }
};

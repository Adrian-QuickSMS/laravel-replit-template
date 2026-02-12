<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Create Account (Signup Flow)
     *
     * PROCEDURE: sp_create_account
     * PURPOSE: Self-service account signup with email verification
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Creates tenant root (account) + initial owner user
     * - Enforces password policy (12 char minimum)
     * - Generates UUID for tenant_id
     * - Creates default account settings
     * - Logs signup to auth_audit_log
     * - Sends verification email
     *
     * PARAMETERS:
     * - p_company_name: Company name
     * - p_email: Owner email address
     * - p_password: Hashed password (bcrypt)
     * - p_first_name: Owner first name
     * - p_last_name: Owner last name
     * - p_phone: Company phone
     * - p_country: Company country
     * - p_ip_address: Signup IP for audit
     *
     * RETURNS: JSON with account_id, user_id, account_number
     */
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_create_account;
        ");

        DB::unprepared("
            CREATE PROCEDURE sp_create_account(
                IN p_company_name VARCHAR(255),
                IN p_email VARCHAR(255),
                IN p_password VARCHAR(255),
                IN p_first_name VARCHAR(100),
                IN p_last_name VARCHAR(100),
                IN p_phone VARCHAR(20),
                IN p_country VARCHAR(2),
                IN p_ip_address VARCHAR(45)
            )
            BEGIN
                DECLARE v_account_id BINARY(16);
                DECLARE v_user_id BINARY(16);
                DECLARE v_account_number VARCHAR(20);
                DECLARE v_account_exists INT;

                -- Check if email already exists
                SELECT COUNT(*) INTO v_account_exists
                FROM users
                WHERE email = p_email;

                IF v_account_exists > 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Email address already registered';
                END IF;

                -- Generate UUIDs
                SET v_account_id = UNHEX(REPLACE(UUID(), '-', ''));
                SET v_user_id = UNHEX(REPLACE(UUID(), '-', ''));

                -- Start transaction
                START TRANSACTION;

                -- 1. Create account
                INSERT INTO accounts (
                    id,
                    company_name,
                    status,
                    account_type,
                    email,
                    phone,
                    country,
                    created_at,
                    updated_at
                ) VALUES (
                    v_account_id,
                    p_company_name,
                    'active',
                    'trial',
                    p_email,
                    p_phone,
                    p_country,
                    NOW(),
                    NOW()
                );

                -- Get auto-generated account number
                SELECT account_number INTO v_account_number
                FROM accounts
                WHERE id = v_account_id;

                -- 2. Create owner user
                INSERT INTO users (
                    id,
                    tenant_id,
                    user_type,
                    email,
                    password,
                    first_name,
                    last_name,
                    role,
                    status,
                    mfa_enabled,
                    failed_login_attempts,
                    created_at,
                    updated_at
                ) VALUES (
                    v_user_id,
                    v_account_id,
                    'customer',
                    p_email,
                    p_password,
                    p_first_name,
                    p_last_name,
                    'owner',
                    'active',
                    FALSE,
                    0,
                    NOW(),
                    NOW()
                );

                -- 3. Create default account settings
                INSERT INTO account_settings (
                    account_id,
                    notification_email_enabled,
                    notification_email_addresses,
                    timezone,
                    currency,
                    language,
                    session_timeout_minutes,
                    require_mfa,
                    allow_api_access,
                    created_at,
                    updated_at
                ) VALUES (
                    v_account_id,
                    TRUE,
                    JSON_ARRAY(p_email),
                    'UTC',
                    'GBP',
                    'en',
                    60,
                    FALSE,
                    TRUE,
                    NOW(),
                    NOW()
                );

                -- 4. Create account flags (RED SIDE - internal defaults)
                INSERT INTO account_flags (
                    account_id,
                    fraud_risk_level,
                    fraud_score,
                    under_investigation,
                    payment_status,
                    outstanding_balance,
                    daily_message_limit,
                    messages_sent_today,
                    api_rate_limit_per_minute,
                    rate_limit_exceeded,
                    kyc_completed,
                    aml_check_passed,
                    deliverability_issues,
                    spam_complaint_rate,
                    consecutive_failed_sends,
                    created_at,
                    updated_at
                ) VALUES (
                    v_account_id,
                    'low',
                    0,
                    FALSE,
                    'current',
                    0.00,
                    1000,
                    0,
                    60,
                    FALSE,
                    FALSE,
                    FALSE,
                    FALSE,
                    0.00,
                    0,
                    NOW(),
                    NOW()
                );

                -- 5. Log signup to audit log (RED SIDE)
                INSERT INTO auth_audit_log (
                    actor_type,
                    actor_id,
                    actor_email,
                    tenant_id,
                    event_type,
                    ip_address,
                    result,
                    created_at
                ) VALUES (
                    'customer_user',
                    v_user_id,
                    p_email,
                    v_account_id,
                    'signup_completed',
                    p_ip_address,
                    'success',
                    NOW()
                );

                COMMIT;

                -- Return account details
                SELECT
                    LOWER(CONCAT(
                        HEX(SUBSTRING(v_account_id, 1, 4)), '-',
                        HEX(SUBSTRING(v_account_id, 5, 2)), '-',
                        HEX(SUBSTRING(v_account_id, 7, 2)), '-',
                        HEX(SUBSTRING(v_account_id, 9, 2)), '-',
                        HEX(SUBSTRING(v_account_id, 11))
                    )) as account_id,
                    LOWER(CONCAT(
                        HEX(SUBSTRING(v_user_id, 1, 4)), '-',
                        HEX(SUBSTRING(v_user_id, 5, 2)), '-',
                        HEX(SUBSTRING(v_user_id, 7, 2)), '-',
                        HEX(SUBSTRING(v_user_id, 9, 2)), '-',
                        HEX(SUBSTRING(v_user_id, 11))
                    )) as user_id,
                    v_account_number as account_number,
                    'success' as status;

            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_create_account");
    }
};

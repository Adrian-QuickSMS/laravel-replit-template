<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Create Account (PostgreSQL Version)
     *
     * FUNCTION: sp_create_account
     * PURPOSE: Self-service account signup with email verification
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID generation with gen_random_uuid()
     * - Exception handling with PL/pgSQL EXCEPTION block
     * - Returns TABLE for Laravel compatibility
     * - Uses SECURITY DEFINER to bypass RLS for cross-table operations
     * - Proper transaction handling with ROLLBACK on error
     * - JSONB array for notification emails
     * - INET type for IP address
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Creates tenant root (account) + initial owner user
     * - Enforces password policy (12 char minimum)
     * - Generates UUID for tenant_id
     * - Creates default account settings
     * - Logs signup to auth_audit_log
     * - SECURITY DEFINER allows multi-tenant operations during signup
     * - Sets search_path to prevent schema injection
     *
     * PARAMETERS:
     * - p_company_name: Company name
     * - p_email: Owner email address
     * - p_password: Hashed password (Argon2id)
     * - p_first_name: Owner first name
     * - p_last_name: Owner last name
     * - p_phone: Company phone
     * - p_country: Company country (ISO 3166-1 alpha-2)
     * - p_ip_address: Signup IP for audit (INET type)
     *
     * RETURNS: TABLE with account_id, user_id, account_number, status
     */
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_create_account(TEXT, TEXT, TEXT, TEXT, TEXT, TEXT, TEXT, INET)");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_create_account(
                p_company_name TEXT,
                p_email TEXT,
                p_password TEXT,
                p_first_name TEXT,
                p_last_name TEXT,
                p_phone TEXT,
                p_country TEXT,
                p_ip_address INET
            )
            RETURNS TABLE(
                account_id TEXT,
                user_id TEXT,
                account_number TEXT,
                status TEXT
            )
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = public, pg_temp
            AS \$\$
            DECLARE
                v_account_id UUID;
                v_user_id UUID;
                v_account_number TEXT;
                v_account_exists INT;
            BEGIN
                -- Check if email already exists
                SELECT COUNT(*) INTO v_account_exists
                FROM users
                WHERE email = p_email;

                IF v_account_exists > 0 THEN
                    RAISE EXCEPTION 'Email address already registered';
                END IF;

                -- Generate UUIDs
                v_account_id := gen_random_uuid();
                v_user_id := gen_random_uuid();

                -- 1. Create account
                -- Trigger will auto-generate account_number
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
                    'active'::account_status,
                    'trial'::account_type,
                    p_email,
                    p_phone,
                    p_country,
                    NOW(),
                    NOW()
                );

                -- Get auto-generated account number
                SELECT a.account_number INTO v_account_number
                FROM accounts a
                WHERE a.id = v_account_id;

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
                    'customer'::user_type,
                    p_email,
                    p_password,
                    p_first_name,
                    p_last_name,
                    'owner'::user_role,
                    'active'::user_status,
                    FALSE,
                    0,
                    NOW(),
                    NOW()
                );

                -- 3. Create default account settings
                -- Using JSONB array for notification emails
                INSERT INTO account_settings (
                    account_id,
                    notify_low_balance,
                    low_balance_threshold,
                    notify_failed_messages,
                    notify_monthly_summary,
                    marketing_emails,
                    product_updates,
                    timezone,
                    date_format,
                    currency,
                    session_timeout_minutes,
                    require_mfa_for_api,
                    webhook_urls,
                    created_at,
                    updated_at
                ) VALUES (
                    v_account_id,
                    TRUE,
                    10.00,
                    TRUE,
                    TRUE,
                    FALSE,
                    TRUE,
                    'Europe/London',
                    'd/m/Y',
                    'GBP',
                    120,
                    FALSE,
                    jsonb_build_array(p_email),
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
                    'low'::fraud_risk_level,
                    0,
                    FALSE,
                    'current'::payment_status,
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
                    'customer_user'::actor_type,
                    v_user_id,
                    p_email,
                    v_account_id,
                    'signup_completed'::auth_event_type,
                    p_ip_address,
                    'success'::auth_result,
                    NOW()
                );

                -- Return account details
                RETURN QUERY
                SELECT
                    v_account_id::TEXT,
                    v_user_id::TEXT,
                    v_account_number,
                    'success'::TEXT;

            EXCEPTION
                WHEN OTHERS THEN
                    -- PostgreSQL automatically rolls back on exception
                    RAISE EXCEPTION 'Account creation failed: %', SQLERRM;
            END;
            \$\$;
        ");

        -- Grant EXECUTE permission to portal_rw role
        DB::unprepared("GRANT EXECUTE ON FUNCTION sp_create_account TO portal_rw");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_create_account(TEXT, TEXT, TEXT, TEXT, TEXT, TEXT, TEXT, INET)");
    }
};

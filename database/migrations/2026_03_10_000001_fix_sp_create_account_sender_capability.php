<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_create_account(TEXT, TEXT, TEXT, TEXT, TEXT, TEXT, TEXT, INET)");

        DB::unprepared(<<<'PLPGSQL'
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
            AS $$
            DECLARE
                v_account_id UUID;
                v_user_id UUID;
                v_account_number TEXT;
                v_account_exists INT;
            BEGIN
                SELECT COUNT(*) INTO v_account_exists
                FROM users
                WHERE email = p_email;

                IF v_account_exists > 0 THEN
                    RAISE EXCEPTION 'Email address already registered';
                END IF;

                v_account_id := gen_random_uuid();
                v_user_id := gen_random_uuid();

                INSERT INTO accounts (
                    id, company_name, status, account_type,
                    email, phone, country, created_at, updated_at
                ) VALUES (
                    v_account_id, p_company_name,
                    'pending_verification'::account_status, 'trial'::account_type,
                    p_email, p_phone, p_country, NOW(), NOW()
                );

                SELECT a.account_number INTO v_account_number
                FROM accounts a WHERE a.id = v_account_id;

                INSERT INTO users (
                    id, tenant_id, user_type, email, password,
                    first_name, last_name, role, status, sender_capability,
                    mfa_enabled, failed_login_attempts, created_at, updated_at
                ) VALUES (
                    v_user_id, v_account_id, 'customer'::user_type,
                    p_email, p_password, p_first_name, p_last_name,
                    'owner'::user_role, 'active'::user_status,
                    'advanced'::sender_capability_level,
                    FALSE, 0, NOW(), NOW()
                );

                INSERT INTO account_settings (
                    account_id, notify_low_balance, low_balance_threshold,
                    notify_failed_messages, notify_monthly_summary,
                    marketing_emails, product_updates, timezone,
                    date_format, currency, session_timeout_minutes,
                    require_mfa_for_api, webhook_urls, created_at, updated_at
                ) VALUES (
                    v_account_id, TRUE, 10.00,
                    TRUE, TRUE, TRUE, TRUE,
                    'Europe/London', 'DD/MM/YYYY', 'GBP',
                    30, FALSE, '{}', NOW(), NOW()
                );

                INSERT INTO auth_audit_log (
                    account_id, user_id, event_type, ip_address,
                    details, created_at
                ) VALUES (
                    v_account_id, v_user_id, 'account_created',
                    p_ip_address,
                    jsonb_build_object(
                        'company_name', p_company_name,
                        'email', p_email,
                        'status', 'pending_verification'
                    ),
                    NOW()
                );

                RETURN QUERY
                SELECT
                    v_account_id::TEXT AS account_id,
                    v_user_id::TEXT AS user_id,
                    v_account_number AS account_number,
                    'pending_verification'::TEXT AS status;
            END;
            $$;
PLPGSQL
        );
    }

    public function down(): void
    {
    }
};

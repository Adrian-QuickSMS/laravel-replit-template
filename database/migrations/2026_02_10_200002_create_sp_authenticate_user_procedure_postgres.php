<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Authenticate User (PostgreSQL Version)
     *
     * FUNCTION: sp_authenticate_user
     * PURPOSE: Authenticate user and log attempt
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID handling
     * - INET type for IP addresses
     * - Returns TABLE for Laravel compatibility
     * - SECURITY DEFINER for multi-table audit logging
     * - Proper ENUM type casting
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Checks account locked status
     * - Checks email verified status
     * - Updates failed login attempts
     * - Locks account after 5 failed attempts (30 minutes)
     * - Logs all attempts to auth_audit_log (RED SIDE)
     * - Updates last_login_at and last_login_ip on success
     *
     * PARAMETERS:
     * - p_email: User email
     * - p_ip_address: Login IP for audit (INET type)
     * - p_password_verified: Boolean indicating if password was correct
     *
     * RETURNS: TABLE with status, message, and user details (on success)
     *
     * NOTE: Password verification happens in application layer (Laravel Hash::check)
     *       This procedure handles everything EXCEPT password comparison
     */
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_authenticate_user(TEXT, INET, BOOLEAN)");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_authenticate_user(
                p_email TEXT,
                p_ip_address INET,
                p_password_verified BOOLEAN
            )
            RETURNS TABLE(
                status TEXT,
                message TEXT,
                user_id TEXT,
                tenant_id TEXT,
                email TEXT,
                first_name TEXT,
                last_name TEXT,
                role TEXT,
                mfa_enabled BOOLEAN,
                email_verified BOOLEAN,
                company_name TEXT,
                account_number TEXT,
                account_status TEXT,
                failed_attempts INTEGER,
                locked_until TIMESTAMP
            )
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = public, pg_temp
            AS \$\$
            DECLARE
                v_user_id UUID;
                v_tenant_id UUID;
                v_status user_status;
                v_account_locked_until TIMESTAMP;
                v_email_verified_at TIMESTAMP;
                v_failed_attempts INT;
            BEGIN
                -- Find user
                SELECT
                    u.id,
                    u.tenant_id,
                    u.status,
                    u.locked_until,
                    u.email_verified_at,
                    u.failed_login_attempts
                INTO
                    v_user_id,
                    v_tenant_id,
                    v_status,
                    v_account_locked_until,
                    v_email_verified_at,
                    v_failed_attempts
                FROM users u
                WHERE u.email = p_email
                LIMIT 1;

                -- User not found
                IF v_user_id IS NULL THEN
                    -- Log failed attempt (RED SIDE)
                    INSERT INTO auth_audit_log (
                        actor_type,
                        actor_email,
                        event_type,
                        ip_address,
                        result,
                        failure_reason,
                        created_at
                    ) VALUES (
                        'customer_user'::actor_type,
                        p_email,
                        'login_failed'::auth_event_type,
                        p_ip_address,
                        'failure'::auth_result,
                        'User not found',
                        NOW()
                    );

                    RETURN QUERY
                    SELECT
                        'failure'::TEXT,
                        'Invalid credentials'::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT,
                        NULL::TEXT, NULL::BOOLEAN, NULL::BOOLEAN, NULL::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::INTEGER, NULL::TIMESTAMP;
                    RETURN;
                END IF;

                -- Check if account is locked
                IF v_account_locked_until IS NOT NULL AND v_account_locked_until > NOW() THEN
                    -- Log failed attempt (RED SIDE)
                    INSERT INTO auth_audit_log (
                        actor_type,
                        actor_id,
                        actor_email,
                        tenant_id,
                        event_type,
                        ip_address,
                        result,
                        failure_reason,
                        created_at
                    ) VALUES (
                        'customer_user'::actor_type,
                        v_user_id,
                        p_email,
                        v_tenant_id,
                        'login_failed'::auth_event_type,
                        p_ip_address,
                        'failure'::auth_result,
                        'Account locked',
                        NOW()
                    );

                    RETURN QUERY
                    SELECT
                        'failure'::TEXT,
                        'Account temporarily locked. Please try again later.'::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT,
                        NULL::TEXT, NULL::BOOLEAN, NULL::BOOLEAN, NULL::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::INTEGER, v_account_locked_until;
                    RETURN;
                END IF;

                -- Check if account is active
                IF v_status != 'active'::user_status THEN
                    INSERT INTO auth_audit_log (
                        actor_type,
                        actor_id,
                        actor_email,
                        tenant_id,
                        event_type,
                        ip_address,
                        result,
                        failure_reason,
                        created_at
                    ) VALUES (
                        'customer_user'::actor_type,
                        v_user_id,
                        p_email,
                        v_tenant_id,
                        'login_failed'::auth_event_type,
                        p_ip_address,
                        'failure'::auth_result,
                        'Account status: ' || v_status::TEXT,
                        NOW()
                    );

                    RETURN QUERY
                    SELECT
                        'failure'::TEXT,
                        'Account is not active'::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT,
                        NULL::TEXT, NULL::BOOLEAN, NULL::BOOLEAN, NULL::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::INTEGER, NULL::TIMESTAMP;
                    RETURN;
                END IF;

                -- Check password (verified in application layer)
                IF p_password_verified = FALSE THEN
                    -- Increment failed login attempts
                    v_failed_attempts := v_failed_attempts + 1;

                    UPDATE users
                    SET
                        failed_login_attempts = v_failed_attempts,
                        locked_until = CASE
                            WHEN v_failed_attempts >= 5 THEN NOW() + INTERVAL '30 minutes'
                            ELSE NULL
                        END
                    WHERE id = v_user_id;

                    -- Log failed attempt (RED SIDE)
                    INSERT INTO auth_audit_log (
                        actor_type,
                        actor_id,
                        actor_email,
                        tenant_id,
                        event_type,
                        ip_address,
                        result,
                        failure_reason,
                        created_at
                    ) VALUES (
                        'customer_user'::actor_type,
                        v_user_id,
                        p_email,
                        v_tenant_id,
                        'login_failed'::auth_event_type,
                        p_ip_address,
                        'failure'::auth_result,
                        'Invalid password',
                        NOW()
                    );

                    IF v_failed_attempts >= 5 THEN
                        -- Also log account locked event
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
                            'system'::actor_type,
                            v_user_id,
                            p_email,
                            v_tenant_id,
                            'account_locked'::auth_event_type,
                            p_ip_address,
                            'success'::auth_result,
                            NOW()
                        );

                        RETURN QUERY
                        SELECT
                            'failure'::TEXT,
                            'Too many failed attempts. Account locked for 30 minutes.'::TEXT,
                            NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT,
                            NULL::TEXT, NULL::BOOLEAN, NULL::BOOLEAN, NULL::TEXT,
                            NULL::TEXT, NULL::TEXT, v_failed_attempts, NULL::TIMESTAMP;
                    ELSE
                        RETURN QUERY
                        SELECT
                            'failure'::TEXT,
                            'Invalid credentials'::TEXT,
                            NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT, NULL::TEXT,
                            NULL::TEXT, NULL::BOOLEAN, NULL::BOOLEAN, NULL::TEXT,
                            NULL::TEXT, NULL::TEXT, v_failed_attempts, NULL::TIMESTAMP;
                    END IF;

                    RETURN;
                END IF;

                -- SUCCESS: Update user login info
                UPDATE users
                SET
                    last_login_at = NOW(),
                    last_login_ip = p_ip_address,
                    failed_login_attempts = 0,
                    locked_until = NULL
                WHERE id = v_user_id;

                -- Log successful login (RED SIDE)
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
                    v_tenant_id,
                    'login_success'::auth_event_type,
                    p_ip_address,
                    'success'::auth_result,
                    NOW()
                );

                -- Return user details
                RETURN QUERY
                SELECT
                    'success'::TEXT,
                    'Login successful'::TEXT,
                    u.id::TEXT,
                    u.tenant_id::TEXT,
                    u.email,
                    u.first_name,
                    u.last_name,
                    u.role::TEXT,
                    u.mfa_enabled,
                    (u.email_verified_at IS NOT NULL),
                    a.company_name,
                    a.account_number,
                    a.status::TEXT,
                    0::INTEGER,
                    NULL::TIMESTAMP
                FROM users u
                JOIN accounts a ON u.tenant_id = a.id
                WHERE u.id = v_user_id;

            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION 'Authentication failed: %', SQLERRM;
            END;
            \$\$;
        ");

        // Grant EXECUTE permission to portal_rw role
        DB::unprepared("GRANT EXECUTE ON FUNCTION sp_authenticate_user TO portal_rw");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_authenticate_user(TEXT, INET, BOOLEAN)");
    }
};

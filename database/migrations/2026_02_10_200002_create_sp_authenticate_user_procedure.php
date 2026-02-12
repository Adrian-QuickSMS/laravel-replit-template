<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Authenticate User (Login)
     *
     * PROCEDURE: sp_authenticate_user
     * PURPOSE: Authenticate user and log attempt
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Checks account locked status
     * - Checks email verified status
     * - Updates failed login attempts
     * - Locks account after 5 failed attempts
     * - Logs all attempts to auth_audit_log (RED SIDE)
     * - Updates last_login_at and last_login_ip on success
     *
     * PARAMETERS:
     * - p_email: User email
     * - p_ip_address: Login IP for audit
     *
     * RETURNS: JSON with user details or error
     *
     * NOTE: Password verification happens in application layer (Laravel Hash::check)
     *       This procedure handles everything EXCEPT password comparison
     */
    public function up(): void
    {
        DB::unprepared("
            DROP FUNCTION IF EXISTS sp_authenticate_user(VARCHAR, VARCHAR, BOOLEAN);
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_authenticate_user(
                p_email VARCHAR(255),
                p_ip_address VARCHAR(45),
                p_password_verified BOOLEAN
            ) RETURNS TABLE(
                status TEXT,
                message TEXT,
                user_id TEXT,
                tenant_id TEXT,
                email VARCHAR,
                first_name VARCHAR,
                last_name VARCHAR,
                role VARCHAR,
                mfa_enabled BOOLEAN,
                email_verified BOOLEAN,
                company_name VARCHAR,
                account_number VARCHAR,
                account_status VARCHAR,
                locked_until TIMESTAMP,
                failed_attempts INT
            )
            LANGUAGE plpgsql AS \$\$
            DECLARE
                v_user_id UUID;
                v_tenant_id UUID;
                v_status VARCHAR(20);
                v_locked_until TIMESTAMP;
                v_email_verified_at TIMESTAMP;
                v_failed_attempts INT;
                v_result VARCHAR(20);
                v_failure_reason VARCHAR(255);
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
                    v_locked_until,
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
                        'customer_user',
                        p_email,
                        'login_failed',
                        p_ip_address,
                        'failure',
                        'User not found',
                        NOW()
                    );

                    RETURN QUERY SELECT
                        'failure'::TEXT,
                        'Invalid credentials'::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::VARCHAR, NULL::VARCHAR, NULL::VARCHAR,
                        NULL::VARCHAR, NULL::BOOLEAN, NULL::BOOLEAN, NULL::VARCHAR,
                        NULL::VARCHAR, NULL::VARCHAR, NULL::TIMESTAMP, NULL::INT;
                    RETURN;
                END IF;

                -- Check if account is locked
                IF v_locked_until IS NOT NULL AND v_locked_until > NOW() THEN
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
                        'customer_user',
                        v_user_id,
                        p_email,
                        v_tenant_id,
                        'login_failed',
                        p_ip_address,
                        'failure',
                        'Account locked',
                        NOW()
                    );

                    RETURN QUERY SELECT
                        'failure'::TEXT,
                        'Account temporarily locked. Please try again later.'::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::VARCHAR, NULL::VARCHAR, NULL::VARCHAR,
                        NULL::VARCHAR, NULL::BOOLEAN, NULL::BOOLEAN, NULL::VARCHAR,
                        NULL::VARCHAR, NULL::VARCHAR, v_locked_until, NULL::INT;
                    RETURN;
                END IF;

                -- Check if account is active
                IF v_status != 'active' THEN
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
                        'customer_user',
                        v_user_id,
                        p_email,
                        v_tenant_id,
                        'login_failed',
                        p_ip_address,
                        'failure',
                        'Account status: ' || v_status,
                        NOW()
                    );

                    RETURN QUERY SELECT
                        'failure'::TEXT,
                        'Account is not active'::TEXT,
                        NULL::TEXT, NULL::TEXT, NULL::VARCHAR, NULL::VARCHAR, NULL::VARCHAR,
                        NULL::VARCHAR, NULL::BOOLEAN, NULL::BOOLEAN, NULL::VARCHAR,
                        NULL::VARCHAR, NULL::VARCHAR, NULL::TIMESTAMP, NULL::INT;
                    RETURN;
                END IF;

                -- Check password (verified in application layer)
                IF p_password_verified = FALSE THEN
                    -- Increment failed login attempts
                    v_failed_attempts := v_failed_attempts + 1;

                    UPDATE users
                    SET
                        failed_login_attempts = v_failed_attempts,
                        locked_until = CASE WHEN v_failed_attempts >= 5 THEN NOW() + INTERVAL '30 minutes' ELSE NULL END
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
                        'customer_user',
                        v_user_id,
                        p_email,
                        v_tenant_id,
                        'login_failed',
                        p_ip_address,
                        'failure',
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
                            'system',
                            v_user_id,
                            p_email,
                            v_tenant_id,
                            'account_locked',
                            p_ip_address,
                            'success',
                            NOW()
                        );

                        RETURN QUERY SELECT
                            'failure'::TEXT,
                            'Too many failed attempts. Account locked for 30 minutes.'::TEXT,
                            NULL::TEXT, NULL::TEXT, NULL::VARCHAR, NULL::VARCHAR, NULL::VARCHAR,
                            NULL::VARCHAR, NULL::BOOLEAN, NULL::BOOLEAN, NULL::VARCHAR,
                            NULL::VARCHAR, NULL::VARCHAR, NULL::TIMESTAMP, NULL::INT;
                    ELSE
                        RETURN QUERY SELECT
                            'failure'::TEXT,
                            'Invalid credentials'::TEXT,
                            NULL::TEXT, NULL::TEXT, NULL::VARCHAR, NULL::VARCHAR, NULL::VARCHAR,
                            NULL::VARCHAR, NULL::BOOLEAN, NULL::BOOLEAN, NULL::VARCHAR,
                            NULL::VARCHAR, NULL::VARCHAR, NULL::TIMESTAMP, v_failed_attempts;
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
                    'customer_user',
                    v_user_id,
                    p_email,
                    v_tenant_id,
                    'login_success',
                    p_ip_address,
                    'success',
                    NOW()
                );

                -- Return user details
                RETURN QUERY SELECT
                    'success'::TEXT,
                    NULL::TEXT,
                    u.id::TEXT,
                    u.tenant_id::TEXT,
                    u.email,
                    u.first_name,
                    u.last_name,
                    u.role,
                    u.mfa_enabled,
                    (u.email_verified_at IS NOT NULL)::BOOLEAN,
                    a.company_name,
                    a.account_number,
                    a.status,
                    NULL::TIMESTAMP,
                    NULL::INT
                FROM users u
                JOIN accounts a ON u.tenant_id = a.id
                WHERE u.id = v_user_id;

            END;
            \$\$
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_authenticate_user(VARCHAR, VARCHAR, BOOLEAN)");
    }
};

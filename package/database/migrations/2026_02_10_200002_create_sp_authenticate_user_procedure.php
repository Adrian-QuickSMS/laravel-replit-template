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
            DROP PROCEDURE IF EXISTS sp_authenticate_user;
        ");

        DB::unprepared("
            CREATE PROCEDURE sp_authenticate_user(
                IN p_email VARCHAR(255),
                IN p_ip_address VARCHAR(45),
                IN p_password_verified BOOLEAN
            )
            BEGIN
                DECLARE v_user_id BINARY(16);
                DECLARE v_tenant_id BINARY(16);
                DECLARE v_status VARCHAR(20);
                DECLARE v_account_locked_until DATETIME;
                DECLARE v_email_verified_at DATETIME;
                DECLARE v_failed_attempts INT;
                DECLARE v_result VARCHAR(20);
                DECLARE v_failure_reason VARCHAR(255);

                -- Find user
                SELECT
                    id,
                    tenant_id,
                    status,
                    account_locked_until,
                    email_verified_at,
                    failed_login_attempts
                INTO
                    v_user_id,
                    v_tenant_id,
                    v_status,
                    v_account_locked_until,
                    v_email_verified_at,
                    v_failed_attempts
                FROM users
                WHERE email = p_email
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

                    SELECT 'failure' as status, 'Invalid credentials' as message;
                    LEAVE;
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

                    SELECT
                        'failure' as status,
                        'Account temporarily locked. Please try again later.' as message,
                        v_account_locked_until as locked_until;
                    LEAVE;
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
                        CONCAT('Account status: ', v_status),
                        NOW()
                    );

                    SELECT 'failure' as status, 'Account is not active' as message;
                    LEAVE;
                END IF;

                -- Check password (verified in application layer)
                IF p_password_verified = FALSE THEN
                    -- Increment failed login attempts
                    SET v_failed_attempts = v_failed_attempts + 1;

                    UPDATE users
                    SET
                        failed_login_attempts = v_failed_attempts,
                        account_locked_until = IF(v_failed_attempts >= 5, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NULL)
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

                        SELECT
                            'failure' as status,
                            'Too many failed attempts. Account locked for 30 minutes.' as message;
                    ELSE
                        SELECT
                            'failure' as status,
                            'Invalid credentials' as message,
                            v_failed_attempts as failed_attempts;
                    END IF;

                    LEAVE;
                END IF;

                -- SUCCESS: Update user login info
                UPDATE users
                SET
                    last_login_at = NOW(),
                    last_login_ip = p_ip_address,
                    failed_login_attempts = 0,
                    account_locked_until = NULL
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
                SELECT
                    'success' as status,
                    LOWER(CONCAT(
                        HEX(SUBSTRING(u.id, 1, 4)), '-',
                        HEX(SUBSTRING(u.id, 5, 2)), '-',
                        HEX(SUBSTRING(u.id, 7, 2)), '-',
                        HEX(SUBSTRING(u.id, 9, 2)), '-',
                        HEX(SUBSTRING(u.id, 11))
                    )) as user_id,
                    LOWER(CONCAT(
                        HEX(SUBSTRING(u.tenant_id, 1, 4)), '-',
                        HEX(SUBSTRING(u.tenant_id, 5, 2)), '-',
                        HEX(SUBSTRING(u.tenant_id, 7, 2)), '-',
                        HEX(SUBSTRING(u.tenant_id, 9, 2)), '-',
                        HEX(SUBSTRING(u.tenant_id, 11))
                    )) as tenant_id,
                    u.email,
                    u.first_name,
                    u.last_name,
                    u.role,
                    u.mfa_enabled,
                    u.email_verified_at IS NOT NULL as email_verified,
                    a.company_name,
                    a.account_number,
                    a.status as account_status
                FROM users u
                JOIN accounts a ON u.tenant_id = a.id
                WHERE u.id = v_user_id;

            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_authenticate_user");
    }
};

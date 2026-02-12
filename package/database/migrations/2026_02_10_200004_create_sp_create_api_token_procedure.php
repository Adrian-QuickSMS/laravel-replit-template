<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Create API Token
     *
     * PROCEDURE: sp_create_api_token
     * PURPOSE: Create new API token for programmatic access
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Can only create tokens for own tenant
     * - Token hash stored (plain token returned once)
     * - Enforces token name uniqueness per tenant
     * - Logs token creation to audit log
     *
     * PARAMETERS:
     * - p_user_id: User UUID creating the token
     * - p_tenant_id: Tenant UUID
     * - p_name: Token name
     * - p_token_hash: SHA-256 hash of token
     * - p_token_prefix: First 8 chars for identification
     * - p_scopes: JSON array of scopes
     * - p_access_level: read, write, full
     * - p_ip_whitelist: JSON array of IPs (optional)
     * - p_expires_at: Expiry datetime
     *
     * RETURNS: token_id
     */
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_create_api_token;
        ");

        DB::unprepared("
            CREATE PROCEDURE sp_create_api_token(
                IN p_user_id_hex VARCHAR(36),
                IN p_tenant_id_hex VARCHAR(36),
                IN p_name VARCHAR(100),
                IN p_token_hash VARCHAR(64),
                IN p_token_prefix VARCHAR(8),
                IN p_scopes JSON,
                IN p_access_level VARCHAR(20),
                IN p_ip_whitelist JSON,
                IN p_expires_at DATETIME
            )
            BEGIN
                DECLARE v_user_id BINARY(16);
                DECLARE v_tenant_id BINARY(16);
                DECLARE v_token_id BINARY(16);
                DECLARE v_actual_tenant_id BINARY(16);
                DECLARE v_name_exists INT;

                -- Convert hex UUIDs to binary
                SET v_user_id = UNHEX(REPLACE(p_user_id_hex, '-', ''));
                SET v_tenant_id = UNHEX(REPLACE(p_tenant_id_hex, '-', ''));
                SET v_token_id = UNHEX(REPLACE(UUID(), '-', ''));

                -- Verify user belongs to tenant
                SELECT tenant_id INTO v_actual_tenant_id
                FROM users
                WHERE id = v_user_id
                LIMIT 1;

                IF v_actual_tenant_id IS NULL THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'User not found';
                END IF;

                IF v_actual_tenant_id != v_tenant_id THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Unauthorized: Tenant mismatch';
                END IF;

                -- Check if token name already exists for this tenant
                SELECT COUNT(*) INTO v_name_exists
                FROM api_tokens
                WHERE tenant_id = v_tenant_id
                  AND name = p_name
                  AND revoked_at IS NULL;

                IF v_name_exists > 0 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Token name already exists';
                END IF;

                START TRANSACTION;

                -- Create API token
                INSERT INTO api_tokens (
                    id,
                    tenant_id,
                    user_id,
                    name,
                    token_hash,
                    token_prefix,
                    scopes,
                    access_level,
                    ip_whitelist,
                    expires_at,
                    created_at,
                    updated_at
                ) VALUES (
                    v_token_id,
                    v_tenant_id,
                    v_user_id,
                    p_name,
                    p_token_hash,
                    p_token_prefix,
                    p_scopes,
                    p_access_level,
                    p_ip_whitelist,
                    p_expires_at,
                    NOW(),
                    NOW()
                );

                -- Log to audit log (RED SIDE)
                INSERT INTO auth_audit_log (
                    actor_type,
                    actor_id,
                    tenant_id,
                    event_type,
                    ip_address,
                    result,
                    metadata,
                    created_at
                ) VALUES (
                    'customer_user',
                    v_user_id,
                    v_tenant_id,
                    'api_token_created',
                    '',
                    'success',
                    JSON_OBJECT('token_name', p_name, 'access_level', p_access_level),
                    NOW()
                );

                COMMIT;

                -- Return token ID
                SELECT
                    LOWER(CONCAT(
                        HEX(SUBSTRING(v_token_id, 1, 4)), '-',
                        HEX(SUBSTRING(v_token_id, 5, 2)), '-',
                        HEX(SUBSTRING(v_token_id, 7, 2)), '-',
                        HEX(SUBSTRING(v_token_id, 9, 2)), '-',
                        HEX(SUBSTRING(v_token_id, 11))
                    )) as token_id,
                    'success' as status;

            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS sp_create_api_token");
    }
};

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
            DROP FUNCTION IF EXISTS sp_create_api_token(VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, JSONB, VARCHAR, JSONB, TIMESTAMP);
        ");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_create_api_token(
                p_user_id_hex VARCHAR(36),
                p_tenant_id_hex VARCHAR(36),
                p_name VARCHAR(100),
                p_token_hash VARCHAR(64),
                p_token_prefix VARCHAR(8),
                p_scopes JSONB,
                p_access_level VARCHAR(20),
                p_ip_whitelist JSONB,
                p_expires_at TIMESTAMP
            ) RETURNS TABLE(token_id TEXT, status TEXT)
            LANGUAGE plpgsql AS \$\$
            DECLARE
                v_user_id UUID;
                v_tenant_id UUID;
                v_token_id UUID;
                v_actual_tenant_id UUID;
                v_name_exists INT;
            BEGIN
                -- Convert hex UUIDs to UUID type
                v_user_id := p_user_id_hex::UUID;
                v_tenant_id := p_tenant_id_hex::UUID;
                v_token_id := gen_random_uuid();

                -- Verify user belongs to tenant
                SELECT u.tenant_id INTO v_actual_tenant_id
                FROM users u
                WHERE u.id = v_user_id
                LIMIT 1;

                IF v_actual_tenant_id IS NULL THEN
                    RAISE EXCEPTION 'User not found';
                END IF;

                IF v_actual_tenant_id != v_tenant_id THEN
                    RAISE EXCEPTION 'Unauthorized: Tenant mismatch';
                END IF;

                -- Check if token name already exists for this tenant
                SELECT COUNT(*) INTO v_name_exists
                FROM api_tokens
                WHERE tenant_id = v_tenant_id
                  AND name = p_name
                  AND revoked_at IS NULL;

                IF v_name_exists > 0 THEN
                    RAISE EXCEPTION 'Token name already exists';
                END IF;

                -- Create API token
                INSERT INTO api_tokens (
                    id,
                    tenant_id,
                    created_by_user_id,
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
                    jsonb_build_object('token_name', p_name, 'access_level', p_access_level),
                    NOW()
                );

                -- Return token ID
                RETURN QUERY SELECT
                    v_token_id::TEXT,
                    'success'::TEXT;

            END;
            \$\$
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_create_api_token(VARCHAR, VARCHAR, VARCHAR, VARCHAR, VARCHAR, JSONB, VARCHAR, JSONB, TIMESTAMP)");
    }
};

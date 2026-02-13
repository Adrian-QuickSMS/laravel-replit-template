<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Stored Procedure - Create API Token (PostgreSQL Version)
     *
     * FUNCTION: sp_create_api_token
     * PURPOSE: Create new API token for programmatic access
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID generation and handling
     * - JSONB for scopes and ip_whitelist (queryable)
     * - Returns TABLE for Laravel compatibility
     * - SECURITY DEFINER for audit log access
     * - jsonb_build_object for metadata
     * - ENUM type casting for access_level
     *
     * SECURITY:
     * - Portal users: EXECUTE permission
     * - Can only create tokens for own tenant
     * - Token hash stored (plain token returned once)
     * - Enforces token name uniqueness per tenant
     * - Logs token creation to audit log (RED SIDE)
     *
     * PARAMETERS:
     * - p_user_id: User UUID creating the token (TEXT, converted to UUID)
     * - p_tenant_id: Tenant UUID (TEXT, converted to UUID)
     * - p_name: Token name
     * - p_token_hash: SHA-256 hash of token
     * - p_token_prefix: First 8 chars for identification
     * - p_scopes: JSONB array of scopes
     * - p_access_level: readonly, write, admin (TEXT, cast to ENUM)
     * - p_ip_whitelist: JSONB array of IPs (optional)
     * - p_expires_at: Expiry datetime (TEXT, converted to TIMESTAMP)
     *
     * RETURNS: TABLE with token_id and status
     */
    public function up(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_create_api_token(TEXT, TEXT, TEXT, TEXT, TEXT, JSONB, TEXT, JSONB, TEXT)");

        DB::unprepared("
            CREATE OR REPLACE FUNCTION sp_create_api_token(
                p_user_id TEXT,
                p_tenant_id TEXT,
                p_name TEXT,
                p_token_hash TEXT,
                p_token_prefix TEXT,
                p_scopes JSONB,
                p_access_level TEXT,
                p_ip_whitelist JSONB,
                p_expires_at TEXT
            )
            RETURNS TABLE(
                token_id TEXT,
                status TEXT,
                message TEXT
            )
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = public, pg_temp
            AS \$\$
            DECLARE
                v_user_id UUID;
                v_tenant_id UUID;
                v_token_id UUID;
                v_actual_tenant_id UUID;
                v_name_exists INT;
            BEGIN
                -- Convert text UUIDs to UUID type
                v_user_id := p_user_id::UUID;
                v_tenant_id := p_tenant_id::UUID;
                v_token_id := gen_random_uuid();

                -- Verify user belongs to tenant
                SELECT tenant_id INTO v_actual_tenant_id
                FROM users
                WHERE id = v_user_id
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
                    status,
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
                    p_access_level::api_token_access_level,
                    p_ip_whitelist,
                    'active'::api_token_status,
                    CASE WHEN p_expires_at IS NOT NULL THEN p_expires_at::TIMESTAMP ELSE NULL END,
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
                    'customer_user'::actor_type,
                    v_user_id,
                    v_tenant_id,
                    'api_token_created'::auth_event_type,
                    '0.0.0.0'::INET,
                    'success'::auth_result,
                    jsonb_build_object('token_name', p_name, 'access_level', p_access_level),
                    NOW()
                );

                -- Return token ID
                RETURN QUERY
                SELECT
                    v_token_id::TEXT,
                    'success'::TEXT,
                    'API token created successfully'::TEXT;

            EXCEPTION
                WHEN OTHERS THEN
                    RAISE EXCEPTION 'Token creation failed: %', SQLERRM;
            END;
            \$\$;
        ");

        // Grant EXECUTE permission to portal_rw role
        DB::unprepared("GRANT EXECUTE ON FUNCTION sp_create_api_token TO portal_rw");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS sp_create_api_token(TEXT, TEXT, TEXT, TEXT, TEXT, JSONB, TEXT, JSONB, TEXT)");
    }
};

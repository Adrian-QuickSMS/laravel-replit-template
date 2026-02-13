<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Safe View - API Tokens (PostgreSQL Version)
     *
     * VIEW: api_tokens_view
     * PURPOSE: Portal-safe API token listing
     *
     * POSTGRESQL ENHANCEMENTS:
     * - Native UUID casting (no HEX conversion needed)
     * - JSONB functions (jsonb_array_length instead of JSON_LENGTH)
     * - ENUM types cast to TEXT for JSON serialization
     * - Cleaner, more readable SQL
     *
     * SECURITY:
     * - Portal users: SELECT permission
     * - NEVER exposes: token_hash (security critical)
     * - Shows token_prefix only (first 8 chars for identification)
     * - Tenant-scoped via RLS on underlying api_tokens table
     *
     * COLUMNS EXPOSED:
     * - id, tenant_id, created_by_user_id
     * - name, token_prefix (first 8 chars only)
     * - scopes, access_level
     * - has_ip_whitelist (boolean), ip_count (count only, not actual IPs)
     * - last_used_at, last_used_ip
     * - expires_at, revoked_at
     * - is_active (computed)
     * - created_at, updated_at
     *
     * COLUMNS HIDDEN:
     * - token_hash (SHA-256 hash - NEVER expose)
     * - ip_whitelist (privacy - show count only)
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW api_tokens_view AS
            SELECT
                id::text as id,
                tenant_id::text as tenant_id,
                created_by_user_id::text as created_by_user_id,
                name,
                token_prefix,
                scopes,
                access_level::text as access_level,
                CASE
                    WHEN ip_whitelist IS NOT NULL
                         AND jsonb_array_length(ip_whitelist) > 0 THEN TRUE
                    ELSE FALSE
                END as has_ip_whitelist,
                CASE
                    WHEN ip_whitelist IS NOT NULL THEN jsonb_array_length(ip_whitelist)
                    ELSE 0
                END as ip_count,
                last_used_at,
                last_used_ip::text as last_used_ip,
                expires_at,
                revoked_at,
                CASE
                    WHEN revoked_at IS NOT NULL THEN FALSE
                    WHEN expires_at IS NOT NULL AND expires_at <= NOW() THEN FALSE
                    ELSE TRUE
                END as is_active,
                status::text as status,
                created_at,
                updated_at
            FROM api_tokens
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP VIEW IF EXISTS api_tokens_view");
    }
};

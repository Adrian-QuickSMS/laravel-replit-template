<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Safe View - API Tokens
     *
     * VIEW: api_tokens_view
     * PURPOSE: Portal-safe API token listing
     *
     * SECURITY:
     * - Portal users: SELECT permission
     * - NEVER exposes: token_hash (security critical)
     * - Shows token_prefix only (first 8 chars for identification)
     * - Tenant-scoped (users can only see tokens in own tenant)
     *
     * COLUMNS EXPOSED:
     * - id, tenant_id, user_id
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
                LOWER(CONCAT(
                    HEX(SUBSTRING(id, 1, 4)), '-',
                    HEX(SUBSTRING(id, 5, 2)), '-',
                    HEX(SUBSTRING(id, 7, 2)), '-',
                    HEX(SUBSTRING(id, 9, 2)), '-',
                    HEX(SUBSTRING(id, 11))
                )) as id,
                LOWER(CONCAT(
                    HEX(SUBSTRING(tenant_id, 1, 4)), '-',
                    HEX(SUBSTRING(tenant_id, 5, 2)), '-',
                    HEX(SUBSTRING(tenant_id, 7, 2)), '-',
                    HEX(SUBSTRING(tenant_id, 9, 2)), '-',
                    HEX(SUBSTRING(tenant_id, 11))
                )) as tenant_id,
                LOWER(CONCAT(
                    HEX(SUBSTRING(user_id, 1, 4)), '-',
                    HEX(SUBSTRING(user_id, 5, 2)), '-',
                    HEX(SUBSTRING(user_id, 7, 2)), '-',
                    HEX(SUBSTRING(user_id, 9, 2)), '-',
                    HEX(SUBSTRING(user_id, 11))
                )) as user_id,
                name,
                token_prefix,
                scopes,
                access_level,
                CASE
                    WHEN ip_whitelist IS NOT NULL AND JSON_LENGTH(ip_whitelist) > 0 THEN TRUE
                    ELSE FALSE
                END as has_ip_whitelist,
                CASE
                    WHEN ip_whitelist IS NOT NULL THEN JSON_LENGTH(ip_whitelist)
                    ELSE 0
                END as ip_count,
                last_used_at,
                last_used_ip,
                expires_at,
                revoked_at,
                CASE
                    WHEN revoked_at IS NOT NULL THEN FALSE
                    WHEN expires_at IS NOT NULL AND expires_at <= NOW() THEN FALSE
                    ELSE TRUE
                END as is_active,
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

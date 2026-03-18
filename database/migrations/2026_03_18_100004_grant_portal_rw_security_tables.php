<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Grant portal_rw and portal_ro access to new security tables.
 *
 * account_ip_allowlist: customers read/write their own IP entries (RLS enforced)
 * held_messages: read-only for customer portal (system creates/updates via app role)
 * account_settings: needs direct UPDATE for new security columns
 *
 * Follows the pattern in 2026_03_13_160000_grant_portal_rw_country_tables.php
 */
return new class extends Migration
{
    public function up(): void
    {
        // account_ip_allowlist — full CRUD for customer portal
        if (Schema::hasTable('account_ip_allowlist')) {
            $this->safeGrant('GRANT SELECT, INSERT, UPDATE, DELETE ON account_ip_allowlist TO portal_rw');
            $this->safeGrant('GRANT SELECT ON account_ip_allowlist TO portal_ro');
        }

        // held_messages — read-only for customer portal; system writes held messages
        // via the application's default DB role, not portal_rw
        if (Schema::hasTable('held_messages')) {
            $this->safeGrant('GRANT SELECT ON held_messages TO portal_rw');
            $this->safeGrant('GRANT SELECT ON held_messages TO portal_ro');
        }

        // account_settings — ensure portal_rw can UPDATE security columns directly
        // (The SP only covers a subset of columns; new security settings use direct updates)
        if (Schema::hasTable('account_settings')) {
            $this->safeGrant('GRANT SELECT, UPDATE ON account_settings TO portal_rw');
            $this->safeGrant('GRANT SELECT ON account_settings TO portal_ro');
        }
    }

    public function down(): void
    {
        // Revoke only what we granted — use specific privileges, not REVOKE ALL
        $this->safeGrant('REVOKE SELECT, INSERT, UPDATE, DELETE ON account_ip_allowlist FROM portal_rw');
        $this->safeGrant('REVOKE SELECT ON account_ip_allowlist FROM portal_ro');
        $this->safeGrant('REVOKE SELECT ON held_messages FROM portal_rw');
        $this->safeGrant('REVOKE SELECT ON held_messages FROM portal_ro');
        // Don't revoke account_settings — other grants may exist
    }

    /**
     * Execute a GRANT/REVOKE safely — skip if role doesn't exist (dev environments).
     */
    private function safeGrant(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Illuminate\Database\QueryException $e) {
            // 42704 = undefined_object (role doesn't exist)
            if (str_contains($e->getMessage(), '42704')) {
                return;
            }
            throw $e;
        }
    }
};

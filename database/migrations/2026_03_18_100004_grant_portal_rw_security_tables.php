<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Grant portal_rw and portal_ro access to new security tables.
 *
 * account_ip_allowlist: customers read/write their own IP entries (RLS enforced)
 * held_messages: customers can view held messages (RLS enforced), system writes
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
            DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON account_ip_allowlist TO portal_rw');
            DB::statement('GRANT SELECT ON account_ip_allowlist TO portal_ro');
        }

        // held_messages — read + limited write (status updates) for customer portal
        if (Schema::hasTable('held_messages')) {
            DB::statement('GRANT SELECT, INSERT, UPDATE ON held_messages TO portal_rw');
            DB::statement('GRANT SELECT ON held_messages TO portal_ro');
        }

        // account_settings — ensure portal_rw can UPDATE security columns directly
        // (The SP only covers a subset of columns; new security settings use direct updates)
        if (Schema::hasTable('account_settings')) {
            DB::statement('GRANT SELECT, UPDATE ON account_settings TO portal_rw');
            DB::statement('GRANT SELECT ON account_settings TO portal_ro');
        }
    }

    public function down(): void
    {
        DB::statement('REVOKE ALL ON account_ip_allowlist FROM portal_rw');
        DB::statement('REVOKE ALL ON account_ip_allowlist FROM portal_ro');
        DB::statement('REVOKE ALL ON held_messages FROM portal_rw');
        DB::statement('REVOKE ALL ON held_messages FROM portal_ro');
        // Don't revoke account_settings — other grants may exist
    }
};

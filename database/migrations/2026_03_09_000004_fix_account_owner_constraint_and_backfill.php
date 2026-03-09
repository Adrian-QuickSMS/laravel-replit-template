<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix: Make is_account_owner a unique constraint per tenant (only one owner per account).
 * Backfill: Set is_account_owner=true for existing users with role='owner'.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Backfill: mark existing owners
        DB::statement("
            UPDATE users
            SET is_account_owner = true,
                owner_since = created_at
            WHERE role = 'owner'
              AND is_account_owner = false
              AND deleted_at IS NULL
        ");

        // Drop the non-unique partial index
        DB::statement("DROP INDEX IF EXISTS idx_users_account_owner");

        // Create a UNIQUE partial index — enforces at most one owner per tenant
        DB::statement("
            CREATE UNIQUE INDEX idx_users_account_owner
            ON users (tenant_id)
            WHERE is_account_owner = true
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_users_account_owner");

        // Restore the non-unique partial index
        DB::statement("
            CREATE INDEX idx_users_account_owner
            ON users (tenant_id, is_account_owner)
            WHERE is_account_owner = true
        ");
    }
};

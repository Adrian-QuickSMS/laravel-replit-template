<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * ENUM Rollback Safety Net
 *
 * PostgreSQL cannot remove values from ENUM types without recreating them.
 * This migration creates a helper function that can recreate the account_status
 * ENUM if a full rollback to the 4-status model is ever needed.
 *
 * The function is intentionally NOT called automatically — it must be invoked
 * manually by an ops_admin after verifying all data has been migrated back.
 *
 * USAGE (manual, emergency only):
 *   SELECT rollback_account_status_enum();
 *
 * PREREQUISITES:
 *   1. All accounts must be on old statuses (pending_verification, active, suspended, closed)
 *   2. Run the down() of 2026_03_06_000001 first to migrate data back
 *   3. Take a database backup before running this
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION rollback_account_status_enum()
            RETURNS VOID
            LANGUAGE plpgsql
            SECURITY DEFINER
            SET search_path = public, pg_temp
            AS \$\$
            DECLARE
                new_status_count INT;
            BEGIN
                -- Safety check: ensure no accounts use new status values
                SELECT COUNT(*) INTO new_status_count
                FROM accounts
                WHERE status IN ('test_standard', 'test_dynamic', 'active_standard', 'active_dynamic');

                IF new_status_count > 0 THEN
                    RAISE EXCEPTION 'Cannot rollback ENUM: % accounts still use new status values. Run data migration first.', new_status_count;
                END IF;

                -- Drop views that depend on the ENUM
                EXECUTE 'DROP VIEW IF EXISTS account_safe_view CASCADE';

                -- Drop indexes that use the status column
                EXECUTE 'DROP INDEX IF EXISTS idx_accounts_status';
                EXECUTE 'DROP INDEX IF EXISTS idx_accounts_status_type';
                EXECUTE 'DROP INDEX IF EXISTS idx_accounts_test_mode';
                EXECUTE 'DROP INDEX IF EXISTS idx_accounts_live_mode';

                -- Temporarily change column to TEXT
                ALTER TABLE accounts ALTER COLUMN status TYPE TEXT USING status::TEXT;

                -- Drop old ENUM and recreate with original values
                DROP TYPE IF EXISTS account_status;
                CREATE TYPE account_status AS ENUM ('pending_verification', 'active', 'suspended', 'closed');

                -- Change column back to ENUM
                ALTER TABLE accounts ALTER COLUMN status TYPE account_status USING status::account_status;
                ALTER TABLE accounts ALTER COLUMN status SET DEFAULT 'pending_verification';

                -- Recreate indexes
                CREATE INDEX idx_accounts_status ON accounts (status);

                -- Recreate the original safe view
                CREATE OR REPLACE VIEW account_safe_view AS
                SELECT
                    id::text as id,
                    account_number,
                    company_name,
                    status::text as status,
                    account_type::text as account_type,
                    email,
                    phone,
                    address_line1,
                    address_line2,
                    city,
                    postcode,
                    country,
                    vat_number,
                    billing_email,
                    hubspot_company_id,
                    created_at,
                    updated_at
                FROM accounts
                WHERE status IN ('active', 'suspended');

                RAISE NOTICE 'account_status ENUM successfully rolled back to 4-value model';
            END;
            \$\$;
        ");

        // Only ops_admin can execute this function
        DB::unprepared("REVOKE EXECUTE ON FUNCTION rollback_account_status_enum() FROM PUBLIC");
        DB::unprepared("GRANT EXECUTE ON FUNCTION rollback_account_status_enum() TO ops_admin");
    }

    public function down(): void
    {
        DB::unprepared("DROP FUNCTION IF EXISTS rollback_account_status_enum()");
    }
};

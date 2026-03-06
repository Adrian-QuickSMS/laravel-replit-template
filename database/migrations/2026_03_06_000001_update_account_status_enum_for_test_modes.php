<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Update account_status ENUM to support 4 operational statuses:
 *
 * - pending_verification: Signup completed, awaiting fraud/identity check
 * - test_standard:        Test mode with restrictions (approved numbers, disclaimer, registered SenderIDs)
 * - test_dynamic:         Test mode with relaxed SenderID rules (any valid number, any SenderID passing validation)
 * - active_standard:      Live account, registered SenderIDs only
 * - active_dynamic:       Live account, any SenderID passing validation
 * - suspended:            Account suspended (billing or compliance)
 * - closed:               Account permanently closed
 *
 * Trial accounts (old 'trial' account_type) are replaced by test_standard/test_dynamic statuses.
 * billing_type (prepay/postpay) remains a SEPARATE field — not merged into status.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add new values to the account_status enum
        // PostgreSQL requires ALTER TYPE ... ADD VALUE (cannot remove values in a transaction)
        DB::statement("ALTER TYPE account_status ADD VALUE IF NOT EXISTS 'test_standard' AFTER 'pending_verification'");
        DB::statement("ALTER TYPE account_status ADD VALUE IF NOT EXISTS 'test_dynamic' AFTER 'test_standard'");
        DB::statement("ALTER TYPE account_status ADD VALUE IF NOT EXISTS 'active_standard' AFTER 'test_dynamic'");
        DB::statement("ALTER TYPE account_status ADD VALUE IF NOT EXISTS 'active_dynamic' AFTER 'active_standard'");

        // Step 2: Migrate existing data — handle ALL account_type × status combinations
        // Note: We leave 'active' in the enum (PG can't remove values) but migrate all rows off it.
        //
        // Matrix of old status → new status:
        //   active + trial         → test_standard  (conservative: Standard has more guardrails)
        //   active + system        → active_standard (system accounts are always live)
        //   active + prepay        → active_standard
        //   active + postpay       → active_standard
        //   active + (any other)   → active_standard (catch-all for unexpected account_type values)
        //   pending_verification   → pending_verification (unchanged)
        //   suspended              → suspended (unchanged)
        //   closed                 → closed (unchanged)

        // Convert existing trial+active accounts to test_standard
        DB::statement("
            UPDATE accounts
            SET status = 'test_standard'
            WHERE status = 'active'
            AND account_type = 'trial'
        ");

        // Convert ALL remaining 'active' accounts (system, prepay, postpay, any other) to active_standard
        DB::statement("
            UPDATE accounts
            SET status = 'active_standard'
            WHERE status = 'active'
        ");

        // Safety check: log any accounts still on old 'active' status (should be zero)
        // This is a no-op assertion — if any rows match, the migration has a bug
        DB::statement("
            DO \$\$
            DECLARE
                remaining_count INT;
            BEGIN
                SELECT COUNT(*) INTO remaining_count FROM accounts WHERE status = 'active';
                IF remaining_count > 0 THEN
                    RAISE WARNING 'Migration incomplete: % accounts still have status=active', remaining_count;
                END IF;
            END \$\$
        ");

        // Step 3: Update the account_safe_view to include new statuses
        DB::unprepared("
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
            WHERE status IN ('test_standard', 'test_dynamic', 'active_standard', 'active_dynamic', 'suspended')
        ");

        // Step 4: Update indexes for new status values
        DB::statement("DROP INDEX IF EXISTS idx_accounts_status");
        DB::statement("DROP INDEX IF EXISTS idx_accounts_status_type");
        DB::statement("CREATE INDEX idx_accounts_status ON accounts (status)");
        DB::statement("CREATE INDEX idx_accounts_status_type ON accounts (status, account_type)");
        DB::statement("CREATE INDEX idx_accounts_test_mode ON accounts (status) WHERE status IN ('test_standard', 'test_dynamic')");
        DB::statement("CREATE INDEX idx_accounts_live_mode ON accounts (status) WHERE status IN ('active_standard', 'active_dynamic')");
    }

    public function down(): void
    {
        // Reverse the data migration — PRESERVE test/live distinction via account_type
        // so the old system can still differentiate trial accounts from live accounts.
        // Without this, test accounts would get full live access on rollback.

        // Test accounts → active + account_type='trial' (old system's trial indicator)
        DB::statement("
            UPDATE accounts
            SET status = 'active', account_type = 'trial'
            WHERE status IN ('test_standard', 'test_dynamic')
        ");

        // Live accounts → active (keep existing account_type)
        DB::statement("
            UPDATE accounts
            SET status = 'active'
            WHERE status IN ('active_standard', 'active_dynamic')
        ");

        // Restore the original view
        DB::unprepared("
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
            WHERE status IN ('active', 'suspended')
        ");

        // Drop new indexes
        DB::statement("DROP INDEX IF EXISTS idx_accounts_test_mode");
        DB::statement("DROP INDEX IF EXISTS idx_accounts_live_mode");

        // Note: PostgreSQL cannot remove values from an ENUM type without recreating it.
        // The old values ('active') remain in the type but the new values
        // (test_standard, test_dynamic, active_standard, active_dynamic) also remain.
        // A full rollback would require recreating the type, which is destructive.
    }
};

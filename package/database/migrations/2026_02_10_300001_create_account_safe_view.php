<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * GREEN SIDE: Safe View - Account
     *
     * VIEW: account_safe_view
     * PURPOSE: Portal-safe account data (excludes internal fields)
     *
     * SECURITY:
     * - Portal users: SELECT permission
     * - Excludes: suspended_at, closed_at (internal status tracking)
     * - Tenant-scoped (users can only see own account)
     *
     * COLUMNS EXPOSED:
     * - id, account_number, company_name, status, account_type
     * - email, phone, address fields
     * - vat_number, billing_email
     * - hubspot_company_id (for sync status)
     * - onboarded_at, created_at, updated_at
     *
     * COLUMNS HIDDEN:
     * - suspended_at, closed_at (internal status dates)
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE OR REPLACE VIEW account_safe_view AS
            SELECT
                LOWER(CONCAT(
                    HEX(SUBSTRING(id, 1, 4)), '-',
                    HEX(SUBSTRING(id, 5, 2)), '-',
                    HEX(SUBSTRING(id, 7, 2)), '-',
                    HEX(SUBSTRING(id, 9, 2)), '-',
                    HEX(SUBSTRING(id, 11))
                )) as id,
                account_number,
                company_name,
                status,
                account_type,
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
                onboarded_at,
                created_at,
                updated_at
            FROM accounts
            WHERE status IN ('active', 'suspended')
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP VIEW IF EXISTS account_safe_view");
    }
};

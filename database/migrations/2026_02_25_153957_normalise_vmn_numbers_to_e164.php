<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Convert GB VMN numbers from national format (07XXXXXXXXX)
     * to E.164 without '+' (447XXXXXXXXX) across all three tables.
     *
     * Rule: country_iso = 'GB' AND number starts with '07'
     *   → strip leading '0', prepend '44'
     */
    public function up(): void
    {
        // purchased_numbers — VMNs and dedicated shortcodes (not shared shortcodes like 60866)
        DB::unprepared("
            UPDATE purchased_numbers
            SET number = '44' || substring(number FROM 2)
            WHERE country_iso = 'GB'
              AND number LIKE '07%'
              AND number_type IN ('vmn', 'dedicated_shortcode')
              AND deleted_at IS NULL
        ");

        // sender_ids — NUMERIC type (VMN-backed), national format
        DB::unprepared("
            UPDATE sender_ids
            SET sender_id_value = '44' || substring(sender_id_value FROM 2)
            WHERE sender_type = 'NUMERIC'
              AND sender_id_value LIKE '07%'
              AND deleted_at IS NULL
        ");

        // vmn_pool — all GB numbers in national format
        DB::unprepared("
            UPDATE vmn_pool
            SET number = '44' || substring(number FROM 2)
            WHERE country_iso = 'GB'
              AND number LIKE '07%'
        ");
    }

    public function down(): void
    {
        // Reverse: 447XXXXXXXXX → 07XXXXXXXXX
        DB::unprepared("
            UPDATE purchased_numbers
            SET number = '0' || substring(number FROM 3)
            WHERE country_iso = 'GB'
              AND number LIKE '447%'
              AND number_type IN ('vmn', 'dedicated_shortcode')
              AND deleted_at IS NULL
        ");

        DB::unprepared("
            UPDATE sender_ids
            SET sender_id_value = '0' || substring(sender_id_value FROM 3)
            WHERE sender_type = 'NUMERIC'
              AND sender_id_value LIKE '447%'
              AND deleted_at IS NULL
        ");

        DB::unprepared("
            UPDATE vmn_pool
            SET number = '0' || substring(number FROM 3)
            WHERE country_iso = 'GB'
              AND number LIKE '447%'
        ");
    }
};

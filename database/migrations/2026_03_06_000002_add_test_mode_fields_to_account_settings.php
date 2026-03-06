<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add test mode fields to account_settings table.
 *
 * - approved_test_numbers: JSON array of E.164 numbers that Test Standard accounts
 *   are allowed to send to. Test Dynamic accounts are unrestricted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_settings', function (Blueprint $table) {
            $table->jsonb('approved_test_numbers')->nullable()
                ->comment('E.164 numbers that Test Standard accounts can send to');
        });
    }

    public function down(): void
    {
        Schema::table('account_settings', function (Blueprint $table) {
            $table->dropColumn('approved_test_numbers');
        });
    }
};

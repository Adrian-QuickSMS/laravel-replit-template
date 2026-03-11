<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('account_settings', 'test_numbers_ever_used')) {
            Schema::table('account_settings', function (Blueprint $table) {
                $table->jsonb('test_numbers_ever_used')->default('[]');
            });
        }

        DB::statement("
            UPDATE account_settings
            SET test_numbers_ever_used = COALESCE(approved_test_numbers, '[]'::jsonb)
            WHERE approved_test_numbers IS NOT NULL
              AND approved_test_numbers != '[]'::jsonb
              AND (test_numbers_ever_used IS NULL OR test_numbers_ever_used = '[]'::jsonb)
        ");
    }

    public function down(): void
    {
        Schema::table('account_settings', function (Blueprint $table) {
            $table->dropColumn('test_numbers_ever_used');
        });
    }
};

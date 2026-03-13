<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE country_control_overrides ALTER COLUMN account_id TYPE uuid USING account_id::text::uuid');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE country_control_overrides ALTER COLUMN account_id TYPE bigint USING account_id::text::bigint');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rcs_assets ALTER COLUMN user_id DROP DEFAULT');
        DB::statement('ALTER TABLE rcs_assets ALTER COLUMN user_id TYPE uuid USING NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rcs_assets ALTER COLUMN user_id TYPE bigint USING NULL');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS validity_period integer");
        DB::statement("ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS sending_window_start time");
        DB::statement("ALTER TABLE campaigns ADD COLUMN IF NOT EXISTS sending_window_end time");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE campaigns DROP COLUMN IF EXISTS validity_period");
        DB::statement("ALTER TABLE campaigns DROP COLUMN IF EXISTS sending_window_start");
        DB::statement("ALTER TABLE campaigns DROP COLUMN IF EXISTS sending_window_end");
    }
};

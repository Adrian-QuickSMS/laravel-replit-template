<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rcs_agents ADD COLUMN IF NOT EXISTS sector VARCHAR(100) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rcs_agents DROP COLUMN IF EXISTS sector');
    }
};

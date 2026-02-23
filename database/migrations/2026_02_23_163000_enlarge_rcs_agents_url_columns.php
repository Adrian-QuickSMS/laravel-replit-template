<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rcs_agents ALTER COLUMN logo_url TYPE TEXT');
        DB::statement('ALTER TABLE rcs_agents ALTER COLUMN hero_url TYPE TEXT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rcs_agents ALTER COLUMN logo_url TYPE VARCHAR(255)');
        DB::statement('ALTER TABLE rcs_agents ALTER COLUMN hero_url TYPE VARCHAR(255)');
    }
};

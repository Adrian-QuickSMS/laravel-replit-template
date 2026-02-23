<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rcs_agent_status_histories ALTER COLUMN user_id TYPE varchar(36)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rcs_agent_status_histories ALTER COLUMN user_id TYPE bigint USING user_id::bigint');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE campaigns DROP CONSTRAINT IF EXISTS chk_campaigns_status");
        DB::statement("ALTER TABLE campaigns ADD CONSTRAINT chk_campaigns_status CHECK (status IN ('draft', 'scheduled', 'queued', 'sending', 'paused', 'completed', 'cancelled', 'failed', 'archived'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE campaigns DROP CONSTRAINT IF EXISTS chk_campaigns_status");
        DB::statement("ALTER TABLE campaigns ADD CONSTRAINT chk_campaigns_status CHECK (status IN ('draft', 'scheduled', 'queued', 'sending', 'paused', 'completed', 'cancelled', 'failed'))");
    }
};

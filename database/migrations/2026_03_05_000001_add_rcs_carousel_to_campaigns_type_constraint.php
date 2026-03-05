<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE campaigns DROP CONSTRAINT IF EXISTS chk_campaigns_type");
        DB::statement("ALTER TABLE campaigns ADD CONSTRAINT chk_campaigns_type CHECK (type IN ('sms', 'rcs_basic', 'rcs_single', 'rcs_carousel'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE campaigns DROP CONSTRAINT IF EXISTS chk_campaigns_type");
        DB::statement("ALTER TABLE campaigns ADD CONSTRAINT chk_campaigns_type CHECK (type IN ('sms', 'rcs_basic', 'rcs_single'))");
    }
};

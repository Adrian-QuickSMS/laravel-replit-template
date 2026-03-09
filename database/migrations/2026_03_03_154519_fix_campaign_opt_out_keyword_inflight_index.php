<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_campaign_opt_out_keyword_inflight');
        DB::statement("
            CREATE UNIQUE INDEX idx_campaign_opt_out_keyword_inflight 
            ON campaigns (opt_out_number_id, opt_out_keyword) 
            WHERE opt_out_keyword IS NOT NULL 
            AND opt_out_number_id IS NOT NULL 
            AND status IN ('queued', 'sending', 'scheduled', 'paused')
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_campaign_opt_out_keyword_inflight');
        DB::statement("
            CREATE UNIQUE INDEX idx_campaign_opt_out_keyword_inflight 
            ON campaigns (opt_out_number_id, opt_out_keyword) 
            WHERE opt_out_keyword IS NOT NULL 
            AND opt_out_number_id IS NOT NULL 
            AND status NOT IN ('completed', 'cancelled', 'failed')
        ");
    }
};

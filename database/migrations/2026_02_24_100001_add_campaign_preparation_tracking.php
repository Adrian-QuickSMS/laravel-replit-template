<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // Campaign preparation tracking columns
        // =====================================================
        Schema::table('campaigns', function (Blueprint $table) {
            // When merge field resolution last completed (null = not yet resolved)
            $table->timestamp('content_resolved_at')->nullable()->after('reservation_id');

            // Async preparation status: null → preparing → ready / failed
            $table->string('preparation_status', 20)->nullable()->after('content_resolved_at');
            $table->integer('preparation_progress')->default(0)->after('preparation_status');
            $table->text('preparation_error')->nullable()->after('preparation_progress');
        });

        DB::statement("ALTER TABLE campaigns DROP CONSTRAINT IF EXISTS chk_campaigns_preparation_status");
        DB::statement("ALTER TABLE campaigns ADD CONSTRAINT chk_campaigns_preparation_status CHECK (preparation_status IS NULL OR preparation_status IN ('preparing', 'ready', 'failed'))");

        // =====================================================
        // Per-recipient encoding column
        // =====================================================
        Schema::table('campaign_recipients', function (Blueprint $table) {
            // Per-recipient encoding after merge field resolution: gsm7 or unicode
            $table->string('encoding', 10)->nullable()->after('segments');
        });

        // Composite index for accurate cost estimation aggregation:
        // SELECT country_iso, segments, COUNT(*) ... GROUP BY country_iso, segments
        DB::statement("CREATE INDEX idx_cr_cost_estimation ON campaign_recipients (campaign_id, status, country_iso, segments) WHERE status = 'pending'");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_cr_cost_estimation");
        DB::statement("ALTER TABLE campaigns DROP CONSTRAINT IF EXISTS chk_campaigns_preparation_status");

        Schema::table('campaign_recipients', function (Blueprint $table) {
            $table->dropColumn('encoding');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'content_resolved_at',
                'preparation_status',
                'preparation_progress',
                'preparation_error',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Help Centre — Platform Updates feed.
 *
 * platform_updates:        Global, non-tenant-scoped announcements (maintenance,
 *                          feature, general update). Read by all customers.
 * platform_update_reads:   Per-user read receipts. user_id derived from auth()
 *                          server-side; no RLS needed because lookups are
 *                          always filtered by the current user's id.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 20)->default('update'); // update | maintenance | feature
            $table->string('title');
            $table->text('body');
            $table->timestamp('posted_at')->useCurrent()->index();
            $table->string('link_url', 500)->nullable();
            $table->boolean('published')->default(true);
            $table->timestamps();

            $table->index(['published', 'posted_at']);
        });

        Schema::create('platform_update_reads', function (Blueprint $table) {
            $table->uuid('platform_update_id');
            $table->uuid('user_id');
            $table->timestamp('read_at')->useCurrent();

            $table->primary(['platform_update_id', 'user_id']);
            $table->foreign('platform_update_id')
                ->references('id')->on('platform_updates')
                ->cascadeOnDelete();
            $table->index('user_id');
        });

        // Both tables are readable by portal roles; only ops_admin writes to
        // platform_updates. Reads pivot is read/written by portal_rw because
        // customers create their own read receipts via the API.
        try {
            DB::statement("GRANT SELECT ON platform_updates TO portal_ro");
            DB::statement("GRANT SELECT ON platform_updates TO portal_rw");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON platform_updates TO ops_admin");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON platform_update_reads TO portal_rw");
            DB::statement("GRANT SELECT ON platform_update_reads TO portal_ro");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON platform_update_reads TO ops_admin");
        } catch (\Throwable $e) {
            // Roles may not exist in all environments (e.g. fresh local dev) —
            // grants are optional and do not block table creation.
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_update_reads');
        Schema::dropIfExists('platform_updates');
    }
};

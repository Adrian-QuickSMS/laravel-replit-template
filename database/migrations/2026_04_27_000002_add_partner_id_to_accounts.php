<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add partner_id to accounts — Phase 0, PR-1
 *
 * Existing rows: partner_id = NULL = "owned by QuickSMS direct".
 * No RLS policy changes in this PR — that comes in PR-3.
 *
 * Until the partner-scope predicate ships, partner_id is purely a metadata
 * column. Customer-facing queries are unaffected because RLS still filters
 * by app.current_tenant_id only, and the column does not appear in any
 * controller or view.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('accounts')) {
            return;
        }

        if (! Schema::hasColumn('accounts', 'partner_id')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->uuid('partner_id')->nullable()->after('id');

                $table->foreign('partner_id')
                    ->references('id')->on('partners')
                    ->nullOnDelete();

                $table->index('partner_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('accounts', 'partner_id')) {
            return;
        }

        Schema::table('accounts', function (Blueprint $table) {
            // Drop FK before column (Postgres tolerates either order, but be explicit)
            $table->dropForeign(['partner_id']);
            $table->dropIndex(['partner_id']);
            $table->dropColumn('partner_id');
        });
    }
};

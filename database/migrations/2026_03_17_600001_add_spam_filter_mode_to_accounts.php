<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('spam_filter_mode', 20)->default('enforced')->after('status');
        });

        DB::statement("ALTER TABLE accounts ADD CONSTRAINT chk_spam_filter_mode CHECK (spam_filter_mode IN ('enforced', 'monitoring', 'off'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE accounts DROP CONSTRAINT IF EXISTS chk_spam_filter_mode");

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('spam_filter_mode');
        });
    }
};

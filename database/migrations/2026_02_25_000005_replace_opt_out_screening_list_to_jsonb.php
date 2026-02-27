<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('opt_out_screening_list_id');
        });

        DB::statement("ALTER TABLE campaigns ADD COLUMN opt_out_screening_list_ids jsonb NOT NULL DEFAULT '[]'::jsonb");
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('opt_out_screening_list_ids');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->uuid('opt_out_screening_list_id')->nullable()->after('opt_out_list_id');
        });
    }
};

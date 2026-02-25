<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->uuid('opt_out_screening_list_id')->nullable()->after('opt_out_list_id')
                ->comment('FK to opt_out_lists — list screened against to exclude recipients before send');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('opt_out_screening_list_id');
        });
    }
};

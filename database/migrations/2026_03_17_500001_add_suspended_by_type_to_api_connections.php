<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_connections', function (Blueprint $table) {
            $table->string('suspended_by_type', 20)->nullable()->after('suspended_by');
        });
    }

    public function down(): void
    {
        Schema::table('api_connections', function (Blueprint $table) {
            $table->dropColumn('suspended_by_type');
        });
    }
};

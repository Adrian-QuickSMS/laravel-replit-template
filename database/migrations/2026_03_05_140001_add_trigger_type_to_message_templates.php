<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->string('trigger_type', 20)->default('portal')->after('type');
        });

        DB::statement("UPDATE message_templates SET trigger_type = 'portal' WHERE trigger_type IS NULL OR trigger_type = ''");
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn('trigger_type');
        });
    }
};

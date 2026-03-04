<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->boolean('social_hours_enabled')->default(false)->after('message_expiry_value');
            $table->string('social_hours_from', 5)->nullable()->after('social_hours_enabled');
            $table->string('social_hours_to', 5)->nullable()->after('social_hours_from');
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn(['social_hours_enabled', 'social_hours_from', 'social_hours_to']);
        });
    }
};

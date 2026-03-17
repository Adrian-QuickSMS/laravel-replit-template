<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('type')->index();
            $table->string('action_url')->nullable()->after('deep_link');
            $table->string('action_label')->nullable()->after('action_url');
        });

        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('type')->index();
            $table->string('action_url')->nullable()->after('deep_link');
            $table->string('action_label')->nullable()->after('action_url');
            $table->timestamp('dismissed_at')->nullable()->after('read_at');
            $table->timestamp('resolved_at')->nullable()->after('dismissed_at');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['category', 'action_url', 'action_label']);
        });

        Schema::table('admin_notifications', function (Blueprint $table) {
            $table->dropColumn(['category', 'action_url', 'action_label', 'dismissed_at', 'resolved_at']);
        });
    }
};

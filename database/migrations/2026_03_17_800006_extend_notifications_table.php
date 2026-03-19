<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'category')) {
                $table->string('category', 50)->nullable()->after('type')->index();
            }
            if (!Schema::hasColumn('notifications', 'action_url')) {
                $table->string('action_url')->nullable()->after('deep_link');
            }
            if (!Schema::hasColumn('notifications', 'action_label')) {
                $table->string('action_label')->nullable()->after('action_url');
            }
        });

        Schema::table('admin_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_notifications', 'category')) {
                $table->string('category', 50)->nullable()->after('type')->index();
            }
            if (!Schema::hasColumn('admin_notifications', 'action_url')) {
                $table->string('action_url')->nullable()->after('deep_link');
            }
            if (!Schema::hasColumn('admin_notifications', 'action_label')) {
                $table->string('action_label')->nullable()->after('action_url');
            }
            if (!Schema::hasColumn('admin_notifications', 'dismissed_at')) {
                $table->timestamp('dismissed_at')->nullable()->after('read_at');
            }
            if (!Schema::hasColumn('admin_notifications', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('dismissed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $columns = array_filter(
                ['category', 'action_url', 'action_label'],
                fn ($col) => Schema::hasColumn('notifications', $col)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('admin_notifications', function (Blueprint $table) {
            $columns = array_filter(
                ['category', 'action_url', 'action_label', 'dismissed_at', 'resolved_at'],
                fn ($col) => Schema::hasColumn('admin_notifications', $col)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

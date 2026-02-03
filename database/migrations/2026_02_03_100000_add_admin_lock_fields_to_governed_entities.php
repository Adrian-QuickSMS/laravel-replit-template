<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $lockColumns = function (Blueprint $table) {
            $table->enum('lock_source', ['NONE', 'CUSTOMER', 'ADMIN'])->default('NONE')->after('status');
            $table->text('lock_reason')->nullable()->after('lock_source');
            $table->timestamp('locked_at')->nullable()->after('lock_reason');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
            $table->index(['lock_source']);
        };

        if (Schema::hasTable('templates')) {
            Schema::table('templates', function (Blueprint $table) use ($lockColumns) {
                if (!Schema::hasColumn('templates', 'lock_source')) {
                    $lockColumns($table);
                }
            });
        }

        if (Schema::hasTable('sender_ids')) {
            Schema::table('sender_ids', function (Blueprint $table) use ($lockColumns) {
                if (!Schema::hasColumn('sender_ids', 'lock_source')) {
                    $lockColumns($table);
                }
            });
        }

        if (Schema::hasTable('campaigns')) {
            Schema::table('campaigns', function (Blueprint $table) use ($lockColumns) {
                if (!Schema::hasColumn('campaigns', 'lock_source')) {
                    $lockColumns($table);
                }
            });
        }

        if (Schema::hasTable('api_connections')) {
            Schema::table('api_connections', function (Blueprint $table) use ($lockColumns) {
                if (!Schema::hasColumn('api_connections', 'lock_source')) {
                    $lockColumns($table);
                }
            });
        }

        if (Schema::hasTable('numbers')) {
            Schema::table('numbers', function (Blueprint $table) use ($lockColumns) {
                if (!Schema::hasColumn('numbers', 'lock_source')) {
                    $lockColumns($table);
                }
            });
        }

        if (Schema::hasTable('rcs_agents')) {
            Schema::table('rcs_agents', function (Blueprint $table) use ($lockColumns) {
                if (!Schema::hasColumn('rcs_agents', 'lock_source')) {
                    $lockColumns($table);
                }
            });
        }

        if (Schema::hasTable('email_to_sms_configs')) {
            Schema::table('email_to_sms_configs', function (Blueprint $table) use ($lockColumns) {
                if (!Schema::hasColumn('email_to_sms_configs', 'lock_source')) {
                    $lockColumns($table);
                }
            });
        }
    }

    public function down(): void
    {
        $dropLockColumns = function (Blueprint $table) {
            $table->dropColumn(['lock_source', 'lock_reason', 'locked_at', 'locked_by']);
        };

        $tables = ['templates', 'sender_ids', 'campaigns', 'api_connections', 'numbers', 'rcs_agents', 'email_to_sms_configs'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'lock_source')) {
                Schema::table($tableName, $dropLockColumns);
            }
        }
    }
};

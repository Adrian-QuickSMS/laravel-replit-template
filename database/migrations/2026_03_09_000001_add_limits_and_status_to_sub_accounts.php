<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->integer('message_limit')->nullable()->after('spending_limit')
                ->comment('Monthly message cap. NULL = unlimited');
            $table->integer('daily_limit')->nullable()->after('message_limit')
                ->comment('Daily send limit. NULL = unlimited');
            $table->string('enforcement_type', 20)->default('warn')->after('daily_limit')
                ->comment('warn, block, or approval');
            $table->boolean('hard_stop')->default(false)->after('enforcement_type');
            $table->string('status', 20)->default('live')->after('is_active')
                ->comment('live, suspended, archived');
            $table->timestamp('suspended_at')->nullable()->after('status');
            $table->text('suspended_reason')->nullable()->after('suspended_at');
            $table->timestamp('archived_at')->nullable()->after('suspended_reason');
            $table->integer('messages_used_current_period')->default(0)->after('spending_used_current_period');
        });
    }

    public function down(): void
    {
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'message_limit', 'daily_limit', 'enforcement_type', 'hard_stop',
                'status', 'suspended_at', 'suspended_reason', 'archived_at',
                'messages_used_current_period',
            ]);
        });
    }
};

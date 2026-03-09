<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sender_capability', 20)->default('advanced')->after('role')
                ->comment('advanced or restricted');
            $table->decimal('spend_cap', 12, 4)->nullable()->after('sender_capability')
                ->comment('User-level monthly spend cap. NULL = inherit from sub-account');
            $table->integer('message_cap')->nullable()->after('spend_cap')
                ->comment('User-level monthly message cap. NULL = inherit');
            $table->integer('daily_message_limit')->nullable()->after('message_cap')
                ->comment('User-level daily message limit. NULL = inherit');
            $table->string('user_enforcement_type', 20)->nullable()->after('daily_message_limit')
                ->comment('NULL = inherit from sub-account');
            $table->timestamp('invited_at')->nullable()->after('user_enforcement_type');
            $table->uuid('invited_by')->nullable()->after('invited_at');
            $table->string('invitation_token', 128)->nullable()->after('invited_by');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token');
            $table->timestamp('suspended_at')->nullable()->after('invitation_expires_at');
            $table->text('suspended_reason')->nullable()->after('suspended_at');
            $table->decimal('monthly_spend', 12, 4)->default(0)->after('suspended_reason')
                ->comment('Current period spend tracking');
            $table->integer('monthly_messages')->default(0)->after('monthly_spend')
                ->comment('Current period message count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sender_capability', 'spend_cap', 'message_cap', 'daily_message_limit',
                'user_enforcement_type', 'invited_at', 'invited_by', 'invitation_token',
                'invitation_expires_at', 'suspended_at', 'suspended_reason',
                'monthly_spend', 'monthly_messages',
            ]);
        });
    }
};

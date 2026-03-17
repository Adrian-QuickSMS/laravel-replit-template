<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('overdue_enforcement_mode', 20)->default('hard')->after('spam_filter_mode');
            $table->integer('overdue_grace_days')->default(0)->after('overdue_enforcement_mode');
            $table->string('overdue_email_frequency', 20)->default('weekly')->after('overdue_grace_days');
            $table->timestamp('last_overdue_email_sent_at')->nullable()->after('overdue_email_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'overdue_enforcement_mode',
                'overdue_grace_days',
                'overdue_email_frequency',
                'last_overdue_email_sent_at',
            ]);
        });
    }
};

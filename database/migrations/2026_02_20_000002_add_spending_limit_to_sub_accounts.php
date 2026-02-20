<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->decimal('spending_limit', 12, 4)->nullable()->after('description')
                ->comment('NULL = unlimited spending against parent balance');
            $table->decimal('spending_used_current_period', 12, 4)->default(0)->after('spending_limit');
            $table->timestamp('period_reset_at')->nullable()->after('spending_used_current_period');
        });
    }

    public function down(): void
    {
        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->dropColumn(['spending_limit', 'spending_used_current_period', 'period_reset_at']);
        });
    }
};

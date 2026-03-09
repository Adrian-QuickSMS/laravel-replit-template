<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Add Monthly Spending Cap, Monthly Message Cap, Daily Send Limit,
 * enforcement type, and usage tracking to sub_accounts.
 *
 * Builds on existing spending_limit/spending_used_current_period columns
 * from 2026_02_20_000002_add_spending_limit_to_sub_accounts.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Create enforcement type enum
        DB::statement("CREATE TYPE sub_account_enforcement_type AS ENUM ('warn', 'block', 'approval')");

        // Create sub-account status enum
        DB::statement("CREATE TYPE sub_account_status AS ENUM ('live', 'suspended', 'archived')");

        Schema::table('sub_accounts', function (Blueprint $table) {
            // Monthly Spending Cap (£) — NULL = unlimited
            $table->decimal('monthly_spending_cap', 12, 4)->nullable()->after('spending_used_current_period')
                ->comment('Monthly spending cap in account currency. NULL = unlimited');

            // Monthly Message Cap (message parts) — NULL = unlimited
            $table->integer('monthly_message_cap')->nullable()->after('monthly_spending_cap')
                ->comment('Monthly message cap in parts/fragments. NULL = unlimited');

            // Daily Send Limit (optional) — NULL = no daily limit
            $table->integer('daily_send_limit')->nullable()->after('monthly_message_cap')
                ->comment('Daily send limit in messages. NULL = no daily limit');

            // Usage tracking (aggregated from actual messages)
            $table->decimal('monthly_spend_used', 12, 4)->default(0)->after('daily_send_limit')
                ->comment('Aggregated monthly spend from actual messages sent');
            $table->integer('monthly_messages_used')->default(0)->after('monthly_spend_used')
                ->comment('Aggregated monthly messages sent');
            $table->integer('daily_sends_used')->default(0)->after('monthly_messages_used')
                ->comment('Aggregated daily sends count');
            $table->date('daily_sends_reset_date')->nullable()->after('daily_sends_used')
                ->comment('Date of last daily counter reset');
            $table->date('monthly_usage_reset_date')->nullable()->after('daily_sends_reset_date')
                ->comment('Date of last monthly counter reset');

            // Hard stop toggle
            $table->boolean('hard_stop_enabled')->default(false)->after('monthly_usage_reset_date')
                ->comment('If true, immediately block all sends when any cap reached');

            // Audit fields for limit changes
            $table->string('limits_updated_by')->nullable()->after('hard_stop_enabled');
            $table->timestamp('limits_updated_at')->nullable()->after('limits_updated_by');
        });

        // Add enum columns via raw SQL
        DB::statement("ALTER TABLE sub_accounts ADD COLUMN enforcement_type sub_account_enforcement_type DEFAULT 'warn'");
        DB::statement("ALTER TABLE sub_accounts ADD COLUMN sub_account_status sub_account_status DEFAULT 'live'");

        // Indexes for enforcement checking
        DB::statement("CREATE INDEX idx_sub_accounts_enforcement ON sub_accounts (account_id, enforcement_type)");
        DB::statement("CREATE INDEX idx_sub_accounts_status ON sub_accounts (account_id, sub_account_status)");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_sub_accounts_status");
        DB::statement("DROP INDEX IF EXISTS idx_sub_accounts_enforcement");

        Schema::table('sub_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_spending_cap', 'monthly_message_cap', 'daily_send_limit',
                'monthly_spend_used', 'monthly_messages_used', 'daily_sends_used',
                'daily_sends_reset_date', 'monthly_usage_reset_date',
                'hard_stop_enabled', 'limits_updated_by', 'limits_updated_at',
            ]);
        });

        DB::statement("ALTER TABLE sub_accounts DROP COLUMN IF EXISTS enforcement_type");
        DB::statement("ALTER TABLE sub_accounts DROP COLUMN IF EXISTS sub_account_status");
        DB::statement("DROP TYPE IF EXISTS sub_account_enforcement_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS sub_account_status CASCADE");
    }
};

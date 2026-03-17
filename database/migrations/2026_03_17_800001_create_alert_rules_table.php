<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable()->index();
            $table->uuid('user_id')->nullable();
            $table->string('category', 50)->index(); // billing, messaging, compliance, security, system, campaign
            $table->string('trigger_type', 50); // threshold, percentage_change, absolute_change, event
            $table->string('trigger_key', 100)->index(); // e.g. credit_balance, delivery_rate, api_errors
            $table->string('condition_operator', 30); // lt, gt, lte, gte, eq, drops_by, increases_by
            $table->decimal('condition_value', 14, 4)->nullable(); // threshold/percentage; null for event-based
            $table->jsonb('channels')->default('["in_app"]'); // ["email","in_app","webhook","sms","slack","teams"]
            $table->string('frequency', 30)->default('instant'); // instant, batched_15m, batched_1h, daily_digest, once_per_breach
            $table->integer('cooldown_minutes')->default(60);
            $table->jsonb('escalation_rules')->nullable(); // [{condition_value: 20, channels: ["sms"]}]
            $table->jsonb('recipients')->nullable(); // {roles: [], emails: [], webhook_urls: []}
            $table->boolean('is_enabled')->default(true);
            $table->boolean('is_system_default')->default(false);
            $table->timestamp('last_triggered_at')->nullable();
            $table->decimal('last_value_snapshot', 14, 4)->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'trigger_key', 'is_enabled']);
            $table->index(['trigger_key', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
    }
};

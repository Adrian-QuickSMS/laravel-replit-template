<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('alert_rule_id')->nullable()->index();
            $table->uuid('tenant_id')->nullable()->index();
            $table->string('trigger_key', 100)->index();
            $table->decimal('trigger_value', 14, 4)->nullable();
            $table->decimal('condition_value', 14, 4)->nullable();
            $table->string('severity', 20)->default('info'); // critical, warning, info
            $table->string('category', 50)->nullable();
            $table->string('title');
            $table->text('body')->nullable();
            $table->jsonb('channels_dispatched')->nullable();
            $table->string('status', 30)->default('dispatched'); // dispatched, delivered, failed, suppressed_cooldown, suppressed_frequency
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('alert_rule_id')->references('id')->on('alert_rules')->nullOnDelete();
            $table->index(['tenant_id', 'created_at']);
            $table->index(['trigger_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_history');
    }
};

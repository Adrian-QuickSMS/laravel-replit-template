<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('routing_audit_log')) {
            Schema::create('routing_audit_log', function (Blueprint $table) {
                $table->id();
                $table->string('event_type', 100);
                $table->unsignedBigInteger('routing_rule_id')->nullable();
                $table->unsignedBigInteger('override_id')->nullable();
                $table->unsignedBigInteger('gateway_weight_id')->nullable();
                $table->string('entity_type', 50);
                $table->unsignedBigInteger('entity_id')->nullable();
                $table->string('admin_email');
                $table->string('admin_name')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->json('before_values')->nullable();
                $table->json('after_values')->nullable();
                $table->text('reason')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();

                $table->index(['event_type', 'created_at']);
                $table->index(['routing_rule_id', 'created_at']);
                $table->index(['override_id', 'created_at']);
                $table->index(['admin_email', 'created_at']);
                $table->index(['entity_type', 'entity_id']);
                $table->index(['created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_audit_log');
    }
};

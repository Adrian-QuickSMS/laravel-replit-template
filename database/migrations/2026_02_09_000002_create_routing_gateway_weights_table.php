<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('routing_gateway_weights')) {
            Schema::create('routing_gateway_weights', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('routing_rule_id');
                $table->unsignedBigInteger('gateway_id');
                $table->unsignedBigInteger('supplier_id');
                $table->integer('weight')->default(100);
                $table->integer('priority_order')->default(1);
                $table->boolean('is_fallback')->default(false);
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
                $table->integer('max_tps')->nullable();
                $table->integer('daily_cap')->nullable();
                $table->integer('current_daily_count')->default(0);
                $table->decimal('cost_per_message_gbp', 10, 6)->nullable();
                $table->json('performance_metrics')->nullable();
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('routing_rule_id')
                    ->references('id')
                    ->on('routing_rules')
                    ->onDelete('cascade');

                $table->foreign('gateway_id')
                    ->references('id')
                    ->on('gateways')
                    ->onDelete('cascade');

                $table->foreign('supplier_id')
                    ->references('id')
                    ->on('suppliers')
                    ->onDelete('cascade');

                $table->unique(['routing_rule_id', 'gateway_id'], 'routing_gw_weight_unique');
                $table->index(['routing_rule_id', 'priority_order']);
                $table->index(['gateway_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_gateway_weights');
    }
};

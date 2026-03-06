<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('routing_rules')) {
            Schema::create('routing_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->enum('rule_type', [
                    'COUNTRY', 'NETWORK', 'PRODUCT', 'ACCOUNT', 'FALLBACK'
                ])->default('COUNTRY');
                $table->enum('status', [
                    'active', 'inactive', 'draft', 'archived'
                ])->default('draft');
                $table->integer('priority')->default(100);
                $table->string('country_iso', 3)->nullable();
                $table->string('country_name')->nullable();
                $table->string('mcc', 4)->nullable();
                $table->string('mnc', 4)->nullable();
                $table->string('product_type', 50)->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('gateway_id')->nullable();
                $table->enum('selection_strategy', [
                    'priority', 'round_robin', 'weighted', 'least_cost', 'failover'
                ])->default('priority');
                $table->json('conditions')->nullable();
                $table->json('time_restrictions')->nullable();
                $table->decimal('rate_cap_gbp', 10, 6)->nullable();
                $table->integer('daily_volume_cap')->nullable();
                $table->boolean('is_default')->default(false);
                $table->string('created_by')->nullable();
                $table->string('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['status', 'priority']);
                $table->index(['rule_type', 'status']);
                $table->index(['country_iso', 'status']);
                $table->index(['mcc', 'mnc']);
                $table->index(['supplier_id']);
                $table->index(['gateway_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_rules');
    }
};

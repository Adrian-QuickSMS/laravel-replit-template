<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('routing_customer_overrides')) {
            Schema::create('routing_customer_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id');
                $table->unsignedBigInteger('sub_account_id')->nullable();
                $table->unsignedBigInteger('routing_rule_id')->nullable();
                $table->string('country_iso', 3)->nullable();
                $table->string('mcc', 4)->nullable();
                $table->string('mnc', 4)->nullable();
                $table->string('product_type', 50)->nullable();
                $table->unsignedBigInteger('forced_gateway_id')->nullable();
                $table->unsignedBigInteger('forced_supplier_id')->nullable();
                $table->unsignedBigInteger('blocked_gateway_id')->nullable();
                $table->enum('override_type', [
                    'force_gateway', 'block_gateway', 'priority_boost', 'custom_rule'
                ])->default('force_gateway');
                $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
                $table->text('reason')->nullable();
                $table->date('valid_from')->nullable();
                $table->date('valid_to')->nullable();
                $table->string('created_by')->nullable();
                $table->string('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('routing_rule_id')
                    ->references('id')
                    ->on('routing_rules')
                    ->onDelete('set null');

                $table->foreign('forced_gateway_id')
                    ->references('id')
                    ->on('gateways')
                    ->onDelete('set null');

                $table->foreign('forced_supplier_id')
                    ->references('id')
                    ->on('suppliers')
                    ->onDelete('set null');

                $table->foreign('blocked_gateway_id')
                    ->references('id')
                    ->on('gateways')
                    ->onDelete('set null');

                $table->index(['account_id', 'status']);
                $table->index(['account_id', 'sub_account_id', 'status'], 'routing_override_acct_sub');
                $table->index(['country_iso', 'status']);
                $table->index(['status', 'valid_from', 'valid_to']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('routing_customer_overrides');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('routing_rules', function (Blueprint $table) {
            $table->id();
            $table->enum('product_type', ['SMS', 'RCS_BASIC', 'RCS_SINGLE'])->default('SMS');
            $table->enum('destination_type', ['UK_NETWORK', 'INTERNATIONAL']);
            $table->string('destination_code', 10); // Network prefix for UK or country ISO for international
            $table->string('destination_name'); // Network name (e.g., "Vodafone") or country name
            $table->foreignId('primary_gateway_id')->nullable()->constrained('gateways')->onDelete('set null');
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint: one rule per product + destination combination
            $table->unique(['product_type', 'destination_type', 'destination_code'], 'unique_routing_rule');

            // Indexes for fast lookup during message routing
            $table->index(['product_type', 'destination_type', 'status']);
            $table->index('destination_code');
            $table->index('primary_gateway_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_rules');
    }
};

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
        Schema::create('routing_gateway_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_rule_id')->constrained()->onDelete('cascade');
            $table->foreignId('gateway_id')->constrained()->onDelete('cascade');
            $table->integer('weight')->default(100); // Percentage of traffic (must total 100 across rule)
            $table->enum('route_status', ['allowed', 'blocked'])->default('allowed');
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Unique constraint: one weight entry per routing rule + gateway combination
            $table->unique(['routing_rule_id', 'gateway_id'], 'unique_rule_gateway');

            // Indexes for routing engine lookup
            $table->index(['routing_rule_id', 'route_status', 'weight']);
            $table->index('gateway_id');
            $table->index(['routing_rule_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_gateway_weights');
    }
};

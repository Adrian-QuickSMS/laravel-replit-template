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
        Schema::create('routing_audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('admin_user');
            $table->string('admin_ip', 45);
            $table->enum('action', [
                'route_created',
                'route_edited',
                'primary_changed',
                'weight_changed',
                'gateway_blocked',
                'gateway_unblocked',
                'gateway_added',
                'gateway_removed',
                'destination_blocked',
                'destination_unblocked',
                'override_created',
                'override_edited',
                'override_cancelled',
                'override_expired'
            ]);
            $table->string('entity_type'); // routing_rule, routing_gateway_weight, routing_customer_override
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('product_type', ['SMS', 'RCS_BASIC', 'RCS_SINGLE', 'ALL'])->nullable();
            $table->string('destination')->nullable(); // Network or country name
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('created_at');

            // Indexes for audit queries
            $table->index('admin_user');
            $table->index(['entity_type', 'entity_id']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_audit_log');
    }
};

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
        Schema::create('routing_customer_overrides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id'); // References accounts table
            $table->string('customer_name'); // Denormalized for audit trail
            $table->enum('product_type', ['SMS', 'RCS_BASIC', 'RCS_SINGLE', 'ALL'])->default('ALL');
            $table->enum('scope_type', ['GLOBAL', 'UK_NETWORK', 'COUNTRY'])->default('GLOBAL');
            $table->string('scope_value', 10)->nullable(); // Network code or country ISO if scoped
            $table->foreignId('forced_gateway_id')->constrained('gateways')->onDelete('cascade');
            $table->foreignId('secondary_gateway_id')->nullable()->constrained('gateways')->onDelete('set null');
            $table->datetime('start_datetime');
            $table->datetime('end_datetime')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->text('reason')->nullable();
            $table->boolean('notify_customer')->default(false');
            $table->string('created_by');
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for override resolution during routing
            $table->index(['customer_id', 'status', 'start_datetime', 'end_datetime']);
            $table->index(['product_type', 'scope_type', 'scope_value']);
            $table->index('forced_gateway_id');
            $table->index(['start_datetime', 'end_datetime', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routing_customer_overrides');
    }
};

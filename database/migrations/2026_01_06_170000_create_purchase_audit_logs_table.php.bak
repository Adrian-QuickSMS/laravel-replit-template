<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('audit_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('user_email')->nullable();
            $table->string('user_name')->nullable();
            $table->string('sub_account_id');
            $table->string('sub_account_name')->nullable();
            $table->enum('purchase_type', ['vmn', 'keyword']);
            $table->json('items_purchased');
            $table->json('pricing_details');
            $table->decimal('total_setup_fee', 10, 2);
            $table->decimal('total_monthly_fee', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('transaction_reference')->nullable();
            $table->string('hubspot_order_id')->nullable();
            $table->string('stripe_payment_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('purchased_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('sub_account_id');
            $table->index('purchase_type');
            $table->index('status');
            $table->index('purchased_at');
            $table->index('audit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_audit_logs');
    }
};

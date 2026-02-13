<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_card_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_card_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('gateway_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('action', [
                'supplier_created',
                'supplier_updated',
                'supplier_suspended',
                'gateway_created',
                'gateway_updated',
                'rate_uploaded',
                'rate_created',
                'rate_updated',
                'rate_scheduled',
                'rate_deleted',
                'billing_method_changed',
                'fx_applied'
            ]);
            $table->string('admin_user');
            $table->string('admin_email')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('rate_card_id');
            $table->index('supplier_id');
            $table->index('gateway_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_card_audit_log');
    }
};

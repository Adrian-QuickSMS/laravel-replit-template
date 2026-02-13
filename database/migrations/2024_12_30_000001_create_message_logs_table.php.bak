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
        Schema::create('message_logs', function (Blueprint $table) {
            $table->string('id', 20)->primary();
            $table->string('mobile_number', 20);
            $table->string('sender_id', 15);
            $table->enum('status', ['delivered', 'pending', 'undeliverable', 'rejected']);
            $table->timestamp('sent_time')->nullable();
            $table->timestamp('delivery_time')->nullable();
            $table->timestamp('completed_time')->nullable();
            $table->decimal('cost', 10, 4)->default(0);
            $table->enum('type', ['sms', 'rcs_basic', 'rcs_rich']);
            $table->string('sub_account', 100);
            $table->string('user', 100);
            $table->enum('origin', ['portal', 'api', 'email_to_sms', 'integration']);
            $table->string('country', 2);
            $table->unsignedTinyInteger('fragments')->default(1);
            $table->enum('encoding', ['gsm7', 'unicode']);
            $table->text('content_encrypted');
            $table->boolean('billable_flag')->default(true);
            $table->timestamps();

            $table->index('mobile_number');
            $table->index('sender_id');
            $table->index('status');
            $table->index('sent_time');
            $table->index('type');
            $table->index('sub_account');
            $table->index('origin');
            $table->index('country');
            $table->index(['sent_time', 'status']);
            $table->index(['sub_account', 'sent_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_logs');
    }
};

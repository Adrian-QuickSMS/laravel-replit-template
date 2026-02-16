<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quarantine_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('account_id')->nullable();
            $table->string('sender_id_value', 50)->nullable();
            $table->text('message_body');
            $table->string('primary_engine', 50);
            $table->string('matched_rule_id', 255)->nullable();
            $table->string('matched_rule_name', 255)->nullable();
            $table->string('status', 50)->default('pending');
            $table->string('reviewer_id', 255)->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('primary_engine');
            $table->index('account_id');
            $table->index('created_at');
        });

        Schema::create('quarantine_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quarantine_message_id');
            $table->string('recipient_number', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('quarantine_message_id')
                  ->references('id')
                  ->on('quarantine_messages')
                  ->onDelete('cascade');

            $table->index('quarantine_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarantine_recipients');
        Schema::dropIfExists('quarantine_messages');
    }
};

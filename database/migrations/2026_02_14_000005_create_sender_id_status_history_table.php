<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Sender ID Status History - audit trail for all workflow transitions
 *
 * Follows the RcsAgentStatusHistory pattern.
 *
 * DATA CLASSIFICATION: Internal - Audit Trail
 * SIDE: RED (admin-only for review history)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sender_id_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id_id')->comment('FK to sender_ids.id');
            $table->string('from_status', 20)->nullable()->comment('null for initial creation');
            $table->string('to_status', 20);
            $table->string('action', 50)->comment('Human-readable action name');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->jsonb('payload_snapshot')->nullable()->comment('Full sender ID state at time of transition');

            // Actor information
            $table->uuid('user_id')->nullable()->comment('FK to users.id or admin user');
            $table->string('user_name', 255)->nullable();
            $table->string('user_email', 255)->nullable();
            $table->string('ip_address', 45)->nullable()->comment('IPv4 or IPv6');
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('sender_id_id')->references('id')->on('sender_ids')->onDelete('cascade');

            $table->index('sender_id_id');
            $table->index(['sender_id_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sender_id_status_history');
    }
};

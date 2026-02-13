<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcs_agent_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rcs_agent_id');
            
            $table->enum('from_status', ['draft', 'submitted', 'in_review', 'approved', 'rejected'])->nullable();
            $table->enum('to_status', ['draft', 'submitted', 'in_review', 'approved', 'rejected']);
            
            $table->string('action', 50);
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->json('payload_snapshot')->nullable();
            
            $table->unsignedBigInteger('user_id');
            $table->string('user_name', 100)->nullable();
            $table->string('user_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('rcs_agent_id')
                ->references('id')
                ->on('rcs_agents')
                ->onDelete('cascade');
            
            $table->index(['rcs_agent_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcs_agent_status_histories');
    }
};

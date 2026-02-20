<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * RCS Agent Assignments - polymorphic distribution to sub-accounts and users
 *
 * DATA CLASSIFICATION: Internal - Asset Assignment
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: via rcs_agents.account_id RLS (joined)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcs_agent_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rcs_agent_id')->comment('FK to rcs_agents.id');
            $table->string('assignable_type', 100)->comment('App\\Models\\SubAccount or App\\Models\\User');
            $table->uuid('assignable_id')->comment('UUID of the assigned entity');
            $table->uuid('assigned_by')->nullable()->comment('FK to users.id - who made the assignment');
            $table->timestamps();

            $table->foreign('rcs_agent_id')->references('id')->on('rcs_agents')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');

            // Prevent duplicate assignments
            $table->unique(['rcs_agent_id', 'assignable_type', 'assignable_id'], 'rcs_agent_assignment_unique');

            $table->index(['assignable_type', 'assignable_id']);
            $table->index('rcs_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcs_agent_assignments');
    }
};

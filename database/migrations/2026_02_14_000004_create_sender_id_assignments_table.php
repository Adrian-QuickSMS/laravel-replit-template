<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Sender ID Assignments - polymorphic distribution to sub-accounts and users
 *
 * DATA CLASSIFICATION: Internal - Asset Assignment
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: via sender_ids.account_id RLS (joined)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sender_id_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id_id')->comment('FK to sender_ids.id');
            $table->string('assignable_type', 100)->comment('App\\Models\\SubAccount or App\\Models\\User');
            $table->uuid('assignable_id')->comment('UUID of the assigned entity');
            $table->uuid('assigned_by')->nullable()->comment('FK to users.id - who made the assignment');
            $table->timestamps();

            $table->foreign('sender_id_id')->references('id')->on('sender_ids')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');

            // Prevent duplicate assignments
            $table->unique(['sender_id_id', 'assignable_type', 'assignable_id'], 'sender_id_assignment_unique');

            $table->index(['assignable_type', 'assignable_id']);
            $table->index('sender_id_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sender_id_assignments');
    }
};

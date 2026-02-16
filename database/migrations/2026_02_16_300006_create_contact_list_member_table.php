<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Contact ↔ List Junction Table (Static Lists Only)
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN
 * TENANT ISOLATION: Inherits from parent tables (contacts + contact_lists both RLS-protected)
 *
 * PURPOSE:
 * - Many-to-many: contacts can belong to multiple static lists
 * - Dynamic lists do NOT use this table — membership is computed at query time
 * - Cascade deletes: removing a contact or list cleans up membership
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_list_member', function (Blueprint $table) {
            $table->uuid('contact_id');
            $table->uuid('list_id');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['contact_id', 'list_id']);

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('list_id')->references('id')->on('contact_lists')->onDelete('cascade');

            // Index for reverse lookup (all contacts in a list)
            $table->index('list_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_list_member');
    }
};

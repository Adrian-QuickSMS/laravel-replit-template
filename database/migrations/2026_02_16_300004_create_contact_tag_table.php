<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Contact ↔ Tag Junction Table
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN
 * TENANT ISOLATION: Inherits from parent tables (contacts + tags both RLS-protected)
 *
 * PURPOSE:
 * - Many-to-many relationship: one contact can have many tags, one tag on many contacts
 * - No RLS needed here — both parent tables enforce tenant isolation
 * - Cascade deletes: removing a contact or tag cleans up the junction
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_tag', function (Blueprint $table) {
            $table->uuid('contact_id');
            $table->uuid('tag_id');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['contact_id', 'tag_id']);

            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

            // Index for reverse lookup (all contacts for a tag)
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_tag');
    }
};

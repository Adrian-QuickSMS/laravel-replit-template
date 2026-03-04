<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Add opt-out configuration, sender/agent references, trackable link,
 * and message expiry fields to message_templates.
 *
 * Also fixes the CHECK constraint to include 'rcs_carousel' type.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            // Sender / RCS Agent references (matching Campaign model pattern)
            $table->uuid('sender_id_id')->nullable()->after('segment_count');
            $table->uuid('rcs_agent_id')->nullable()->after('sender_id_id');

            // Opt-out configuration (matching Campaign model pattern)
            $table->boolean('opt_out_enabled')->default(false)->after('rcs_agent_id');
            $table->string('opt_out_method', 20)->nullable()->after('opt_out_enabled'); // reply, url, both
            $table->uuid('opt_out_number_id')->nullable()->after('opt_out_method');
            $table->string('opt_out_keyword', 20)->nullable()->after('opt_out_number_id');
            $table->text('opt_out_text')->nullable()->after('opt_out_keyword');
            $table->uuid('opt_out_list_id')->nullable()->after('opt_out_text');
            $table->boolean('opt_out_url_enabled')->default(false)->after('opt_out_list_id');
            $table->jsonb('opt_out_screening_list_ids')->nullable()->after('opt_out_url_enabled');

            // Trackable link configuration
            $table->boolean('trackable_link_enabled')->default(false)->after('opt_out_screening_list_ids');
            $table->string('trackable_link_domain', 255)->nullable()->after('trackable_link_enabled');

            // Message expiry configuration
            $table->boolean('message_expiry_enabled')->default(false)->after('trackable_link_domain');
            $table->string('message_expiry_value', 10)->nullable()->after('message_expiry_enabled'); // e.g. "24", "48"
        });

        // Fix CHECK constraint: add rcs_carousel to allowed types
        DB::statement("ALTER TABLE message_templates DROP CONSTRAINT IF EXISTS chk_message_templates_type");
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT chk_message_templates_type CHECK (type IN ('sms', 'rcs_basic', 'rcs_single', 'rcs_carousel'))");
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn([
                'sender_id_id',
                'rcs_agent_id',
                'opt_out_enabled',
                'opt_out_method',
                'opt_out_number_id',
                'opt_out_keyword',
                'opt_out_text',
                'opt_out_list_id',
                'opt_out_url_enabled',
                'opt_out_screening_list_ids',
                'trackable_link_enabled',
                'trackable_link_domain',
                'message_expiry_enabled',
                'message_expiry_value',
            ]);
        });

        // Restore original CHECK constraint without rcs_carousel
        DB::statement("ALTER TABLE message_templates DROP CONSTRAINT IF EXISTS chk_message_templates_type");
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT chk_message_templates_type CHECK (type IN ('sms', 'rcs_basic', 'rcs_single'))");
    }
};

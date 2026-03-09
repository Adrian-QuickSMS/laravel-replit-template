<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->integer('version')->default(1)->after('status');
        });

        Schema::create('message_template_versions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('template_id');
            $table->uuid('account_id');
            $table->integer('version');
            $table->jsonb('snapshot');
            $table->text('change_note')->nullable();
            $table->string('edited_by', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('template_id')
                ->references('id')->on('message_templates')
                ->onDelete('cascade');

            $table->unique(['template_id', 'version']);
            $table->index(['template_id', 'created_at']);
            $table->index('account_id');
        });

        Schema::create('message_template_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('template_id');
            $table->uuid('account_id');
            $table->string('action', 30);
            $table->integer('version')->nullable();
            $table->string('user_id', 255)->nullable();
            $table->string('user_name', 255)->nullable();
            $table->text('details')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('template_id')
                ->references('id')->on('message_templates')
                ->onDelete('cascade');

            $table->index(['template_id', 'created_at']);
            $table->index('account_id');
        });

        DB::statement("
            ALTER TABLE message_template_versions ENABLE ROW LEVEL SECURITY;
        ");
        DB::statement("
            ALTER TABLE message_template_versions FORCE ROW LEVEL SECURITY;
        ");
        DB::statement("
            CREATE POLICY tenant_isolation_message_template_versions
            ON message_template_versions
            USING (account_id = current_setting('app.current_tenant_id', true)::uuid);
        ");

        DB::statement("
            ALTER TABLE message_template_audit_log ENABLE ROW LEVEL SECURITY;
        ");
        DB::statement("
            ALTER TABLE message_template_audit_log FORCE ROW LEVEL SECURITY;
        ");
        DB::statement("
            CREATE POLICY tenant_isolation_message_template_audit_log
            ON message_template_audit_log
            USING (account_id = current_setting('app.current_tenant_id', true)::uuid);
        ");

        DB::statement("
            GRANT SELECT, INSERT ON message_template_versions TO portal_rw;
        ");
        DB::statement("
            GRANT SELECT, INSERT ON message_template_audit_log TO portal_rw;
        ");
        DB::statement("
            GRANT ALL ON message_template_versions TO svc_red;
        ");
        DB::statement("
            GRANT ALL ON message_template_audit_log TO svc_red;
        ");

        DB::update("UPDATE message_templates SET version = 1 WHERE version IS NULL OR version = 0");

        $templates = DB::select("SELECT id, account_id, name, description, type, content, rcs_content::text as rcs_content, encoding, character_count, segment_count, status, sender_id_id, rcs_agent_id, opt_out_enabled, opt_out_method, opt_out_number_id, opt_out_keyword, opt_out_text, opt_out_list_id, opt_out_url_enabled, opt_out_screening_list_ids::text as opt_out_screening_list_ids, trackable_link_enabled, trackable_link_domain, message_expiry_enabled, message_expiry_value, social_hours_enabled, social_hours_from, social_hours_to, created_by, created_at FROM message_templates WHERE deleted_at IS NULL");

        foreach ($templates as $t) {
            $snapshot = json_encode([
                'name' => $t->name,
                'description' => $t->description,
                'type' => $t->type,
                'content' => $t->content,
                'rcs_content' => $t->rcs_content ? json_decode($t->rcs_content, true) : null,
                'encoding' => $t->encoding,
                'character_count' => $t->character_count,
                'segment_count' => $t->segment_count,
                'status' => $t->status,
                'sender_id_id' => $t->sender_id_id,
                'rcs_agent_id' => $t->rcs_agent_id,
                'opt_out_enabled' => $t->opt_out_enabled,
                'opt_out_method' => $t->opt_out_method,
                'opt_out_text' => $t->opt_out_text,
                'trackable_link_enabled' => $t->trackable_link_enabled,
                'message_expiry_enabled' => $t->message_expiry_enabled,
                'social_hours_enabled' => $t->social_hours_enabled,
            ]);

            DB::insert("INSERT INTO message_template_versions (id, template_id, account_id, version, snapshot, change_note, edited_by, created_at) VALUES (gen_random_uuid(), ?, ?, 1, ?::jsonb, 'Initial version', ?, ?)", [
                $t->id,
                $t->account_id,
                $snapshot,
                $t->created_by ?? 'System',
                $t->created_at ?? now(),
            ]);

            DB::insert("INSERT INTO message_template_audit_log (id, template_id, account_id, action, version, user_id, user_name, details, created_at) VALUES (gen_random_uuid(), ?, ?, 'created', 1, ?, ?, 'Template created', ?)", [
                $t->id,
                $t->account_id,
                $t->created_by ?? 'system',
                $t->created_by ?? 'System',
                $t->created_at ?? now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::statement("DROP POLICY IF EXISTS tenant_isolation_message_template_audit_log ON message_template_audit_log");
        DB::statement("DROP POLICY IF EXISTS tenant_isolation_message_template_versions ON message_template_versions");

        Schema::dropIfExists('message_template_audit_log');
        Schema::dropIfExists('message_template_versions');

        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};

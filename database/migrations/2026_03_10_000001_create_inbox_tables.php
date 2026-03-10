<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // 1. inbox_conversations — one row per phone+channel+source
        // =====================================================
        Schema::create('inbox_conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('phone_number', 20)->comment('E.164 remote party number');
            $table->string('channel', 10)->default('sms')->comment('sms, rcs');
            $table->string('source', 50)->nullable()->comment('Number/shortcode/agent used (our side)');
            $table->string('source_type', 20)->nullable()->comment('vmn, shortcode, rcs_agent');
            $table->uuid('purchased_number_id')->nullable();
            $table->uuid('rcs_agent_id')->nullable();
            $table->uuid('contact_id')->nullable();
            $table->string('sender_id', 50)->nullable()->comment('Sender ID used for outbound');
            $table->string('status', 20)->default('active')->comment('active, archived, blocked');

            // Denormalised for fast listing
            $table->integer('unread_count')->default(0);
            $table->text('last_message_content')->nullable()->comment('Truncated preview');
            $table->string('last_message_direction', 10)->nullable();
            $table->timestampTz('last_message_at')->nullable();
            $table->timestampTz('first_message_at')->nullable();
            $table->timestampTz('awaiting_reply_since')->nullable()->comment('Set when last msg is inbound');

            $table->timestampsTz();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('purchased_number_id')->references('id')->on('purchased_numbers');
            $table->foreign('contact_id')->references('id')->on('contacts');

            $table->unique(['account_id', 'phone_number', 'channel', 'source'], 'inbox_conv_unique');
            $table->index(['account_id', 'last_message_at'], 'idx_inbox_conv_account_last_msg');
            $table->index(['account_id', 'status', 'unread_count'], 'idx_inbox_conv_account_status_unread');
            $table->index(['account_id', 'phone_number'], 'idx_inbox_conv_account_phone');
        });

        // =====================================================
        // 2. inbox_messages — individual messages within conversations
        // =====================================================
        Schema::create('inbox_messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('conversation_id');
            $table->string('direction', 10)->comment('inbound, outbound');
            $table->string('channel', 10)->default('sms')->comment('sms, rcs');
            $table->string('from_number', 20);
            $table->string('to_number', 20);
            $table->text('content')->nullable()->comment('Plaintext for search');
            $table->text('content_encrypted')->nullable()->comment('Encrypted at rest');
            $table->jsonb('rcs_payload')->nullable()->comment('Rich card: title, description, image, buttons');
            $table->string('status', 20)->default('sent')->comment('sent, delivered, failed, received');
            $table->string('message_log_id', 20)->nullable()->comment('Link to message_logs for outbound');
            $table->string('gateway_message_id', 255)->nullable()->comment('Gateway reference for inbound');
            $table->decimal('cost', 10, 4)->default(0);
            $table->integer('fragments')->default(1);
            $table->string('encoding', 10)->default('gsm7')->comment('gsm7, unicode');
            $table->timestampTz('sent_at')->useCurrent();
            $table->timestampTz('delivered_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('conversation_id')->references('id')->on('inbox_conversations')->onDelete('cascade');

            $table->index(['conversation_id', 'sent_at'], 'idx_inbox_msg_conv_sent');
            $table->index(['account_id', 'sent_at'], 'idx_inbox_msg_account_sent');
            $table->index('gateway_message_id', 'idx_inbox_msg_gateway_id');
        });

        // =====================================================
        // 3. inbox_read_receipts — per-user read tracking
        // =====================================================
        Schema::create('inbox_read_receipts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('conversation_id');
            $table->uuid('user_id');
            $table->timestampTz('last_read_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('conversation_id')->references('id')->on('inbox_conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');

            $table->unique(['conversation_id', 'user_id'], 'inbox_read_unique');
            $table->index(['user_id', 'conversation_id'], 'idx_inbox_read_user');
        });

        // =====================================================
        // UUID auto-generation triggers
        // =====================================================
        $tables = ['inbox_conversations', 'inbox_messages', 'inbox_read_receipts'];
        foreach ($tables as $tbl) {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION generate_uuid_{$tbl}()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;

                CREATE TRIGGER before_insert_{$tbl}_uuid
                BEFORE INSERT ON {$tbl}
                FOR EACH ROW EXECUTE FUNCTION generate_uuid_{$tbl}();
            ");
        }

        // =====================================================
        // Tenant RLS policies
        // =====================================================
        $tenantTables = ['inbox_conversations', 'inbox_messages', 'inbox_read_receipts'];
        foreach ($tenantTables as $tbl) {
            DB::unprepared("
                ALTER TABLE {$tbl} ENABLE ROW LEVEL SECURITY;
                ALTER TABLE {$tbl} FORCE ROW LEVEL SECURITY;

                DROP POLICY IF EXISTS {$tbl}_tenant_isolation ON {$tbl};
                CREATE POLICY {$tbl}_tenant_isolation ON {$tbl}
                    USING (account_id::text = current_setting('app.current_tenant_id', true))
                    WITH CHECK (account_id::text = current_setting('app.current_tenant_id', true));
            ");
        }

        // =====================================================
        // Full-text search index on inbox_messages.content
        // =====================================================
        DB::unprepared("
            CREATE INDEX idx_inbox_msg_content_fts ON inbox_messages
                USING gin(to_tsvector('english', COALESCE(content, '')));
        ");

        $inboxTables = ['inbox_conversations', 'inbox_messages', 'inbox_read_receipts'];
        foreach ($inboxTables as $tbl) {
            DB::unprepared("GRANT SELECT, INSERT, UPDATE, DELETE ON {$tbl} TO portal_rw");
            DB::unprepared("GRANT SELECT ON {$tbl} TO portal_ro");
            DB::unprepared("GRANT ALL ON {$tbl} TO svc_red");
            DB::unprepared("GRANT ALL ON {$tbl} TO ops_admin");
        }
    }

    public function down(): void
    {
        $tenantTables = ['inbox_read_receipts', 'inbox_messages', 'inbox_conversations'];
        foreach ($tenantTables as $tbl) {
            DB::unprepared("DROP POLICY IF EXISTS {$tbl}_tenant_isolation ON {$tbl}");
            DB::unprepared("ALTER TABLE {$tbl} DISABLE ROW LEVEL SECURITY");
        }

        DB::unprepared("DROP INDEX IF EXISTS idx_inbox_msg_content_fts");

        $tables = ['inbox_read_receipts', 'inbox_messages', 'inbox_conversations'];
        foreach ($tables as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
            Schema::dropIfExists($tbl);
        }
    }
};

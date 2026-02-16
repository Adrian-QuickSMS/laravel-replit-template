<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('tenant_id')->comment('FK to accounts.id — required for RLS');
            $table->uuid('user_id')->nullable()->comment('If null, notification is tenant-wide');
            $table->string('type', 100)->comment('e.g. SENDERID_RETURNED');
            $table->string('severity', 20)->default('info')->comment('info, warning, error');
            $table->string('title', 500);
            $table->text('body');
            $table->string('deep_link', 500)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('tenant_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->index('tenant_id');
            $table->index(['tenant_id', 'type']);
            $table->index('created_at');
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_notifications()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.uuid IS NULL THEN
                    NEW.uuid = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_notifications_uuid
            BEFORE INSERT ON notifications
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_notifications();
        ");

        DB::unprepared("ALTER TABLE notifications ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE notifications FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY notifications_isolation ON notifications
            FOR ALL
            USING (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                tenant_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS notifications_isolation ON notifications");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_notifications_uuid ON notifications");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_notifications()");
        Schema::dropIfExists('notifications');
    }
};

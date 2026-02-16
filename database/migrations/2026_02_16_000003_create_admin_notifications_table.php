<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('recipient_admin_id')->nullable()->comment('Null = all admins');
            $table->string('type', 100);
            $table->string('severity', 20)->default('info');
            $table->string('title', 500);
            $table->text('body');
            $table->string('deep_link', 500)->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['type']);
            $table->index('created_at');
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_admin_notifications()
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
            CREATE TRIGGER before_insert_admin_notifications_uuid
            BEFORE INSERT ON admin_notifications
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_admin_notifications();
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_admin_notifications_uuid ON admin_notifications");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_admin_notifications()");
        Schema::dropIfExists('admin_notifications');
    }
};

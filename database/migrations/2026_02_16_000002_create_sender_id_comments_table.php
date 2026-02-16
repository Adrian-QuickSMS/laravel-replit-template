<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sender_id_comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('sender_id_id')->comment('FK to sender_ids.id');
            $table->string('comment_type', 20)->comment('internal or customer');
            $table->text('comment_text');
            $table->string('created_by_actor_type', 20)->comment('admin, customer, or system');
            $table->uuid('created_by_actor_id')->nullable();
            $table->string('created_by_actor_name', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('sender_id_id')->references('id')->on('sender_ids')->onDelete('cascade');

            $table->index(['sender_id_id', 'comment_type']);
            $table->index('created_at');
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_sender_id_comments()
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
            CREATE TRIGGER before_insert_sender_id_comments_uuid
            BEFORE INSERT ON sender_id_comments
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_sender_id_comments();
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_sender_id_comments_uuid ON sender_id_comments");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_sender_id_comments()");
        Schema::dropIfExists('sender_id_comments');
    }
};

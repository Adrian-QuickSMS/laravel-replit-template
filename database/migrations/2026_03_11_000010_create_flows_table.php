<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('created_by');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->unsignedInteger('version')->default(1);
            $table->jsonb('canvas_meta')->nullable();
            $table->timestampTz('last_activated_at')->nullable();
            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('created_by')->references('id')->on('users');

            $table->index('account_id');
            $table->index('status');
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_flows()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER before_insert_flows_uuid
            BEFORE INSERT ON flows
            FOR EACH ROW EXECUTE FUNCTION generate_uuid_flows();
        ");

        DB::unprepared("
            ALTER TABLE flows ENABLE ROW LEVEL SECURITY;
            ALTER TABLE flows FORCE ROW LEVEL SECURITY;

            DROP POLICY IF EXISTS flows_tenant_isolation ON flows;
            CREATE POLICY flows_tenant_isolation ON flows
                USING (account_id::text = current_setting('app.current_tenant_id', true))
                WITH CHECK (account_id::text = current_setting('app.current_tenant_id', true));
        ");

        DB::unprepared("GRANT SELECT, INSERT, UPDATE, DELETE ON flows TO portal_rw");
        DB::unprepared("GRANT SELECT ON flows TO portal_ro");
        DB::unprepared("GRANT ALL ON flows TO svc_red");
        DB::unprepared("GRANT ALL ON flows TO ops_admin");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS flows_tenant_isolation ON flows");
        DB::unprepared("ALTER TABLE flows DISABLE ROW LEVEL SECURITY");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_flows_uuid ON flows");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_flows()");
        Schema::dropIfExists('flows');
    }
};

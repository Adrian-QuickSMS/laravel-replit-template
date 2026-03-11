<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('flow_id');
            $table->string('source_node_uid', 64);
            $table->string('target_node_uid', 64);
            $table->string('source_handle', 50)->default('default');
            $table->string('label')->nullable();
            $table->timestampsTz();

            $table->foreign('flow_id')->references('id')->on('flows')->onDelete('cascade');
            $table->index(['flow_id', 'source_node_uid']);
            $table->index(['flow_id', 'target_node_uid']);
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_flow_connections()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER before_insert_flow_connections_uuid
            BEFORE INSERT ON flow_connections
            FOR EACH ROW EXECUTE FUNCTION generate_uuid_flow_connections();
        ");

        DB::unprepared("
            ALTER TABLE flow_connections ENABLE ROW LEVEL SECURITY;
            ALTER TABLE flow_connections FORCE ROW LEVEL SECURITY;

            DROP POLICY IF EXISTS flow_connections_tenant_isolation ON flow_connections;
            CREATE POLICY flow_connections_tenant_isolation ON flow_connections
                USING (EXISTS (
                    SELECT 1 FROM flows WHERE flows.id = flow_connections.flow_id
                    AND flows.account_id::text = current_setting('app.current_tenant_id', true)
                ))
                WITH CHECK (EXISTS (
                    SELECT 1 FROM flows WHERE flows.id = flow_connections.flow_id
                    AND flows.account_id::text = current_setting('app.current_tenant_id', true)
                ));
        ");

        DB::unprepared("GRANT SELECT, INSERT, UPDATE, DELETE ON flow_connections TO portal_rw");
        DB::unprepared("GRANT SELECT ON flow_connections TO portal_ro");
        DB::unprepared("GRANT ALL ON flow_connections TO svc_red");
        DB::unprepared("GRANT ALL ON flow_connections TO ops_admin");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS flow_connections_tenant_isolation ON flow_connections");
        DB::unprepared("ALTER TABLE flow_connections DISABLE ROW LEVEL SECURITY");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_flow_connections_uuid ON flow_connections");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_flow_connections()");
        Schema::dropIfExists('flow_connections');
    }
};

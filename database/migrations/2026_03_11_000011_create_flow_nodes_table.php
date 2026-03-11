<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('flow_id');
            $table->string('node_uid', 64);
            $table->string('type', 50);
            $table->string('label')->nullable();
            $table->jsonb('config')->nullable();
            $table->decimal('position_x', 10, 2)->default(0);
            $table->decimal('position_y', 10, 2)->default(0);
            $table->timestampsTz();

            $table->foreign('flow_id')->references('id')->on('flows')->onDelete('cascade');
            $table->unique(['flow_id', 'node_uid']);
            $table->index('type');
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_flow_nodes()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER before_insert_flow_nodes_uuid
            BEFORE INSERT ON flow_nodes
            FOR EACH ROW EXECUTE FUNCTION generate_uuid_flow_nodes();
        ");

        DB::unprepared("
            ALTER TABLE flow_nodes ENABLE ROW LEVEL SECURITY;
            ALTER TABLE flow_nodes FORCE ROW LEVEL SECURITY;

            DROP POLICY IF EXISTS flow_nodes_tenant_isolation ON flow_nodes;
            CREATE POLICY flow_nodes_tenant_isolation ON flow_nodes
                USING (EXISTS (
                    SELECT 1 FROM flows WHERE flows.id = flow_nodes.flow_id
                    AND flows.account_id::text = current_setting('app.current_tenant_id', true)
                ))
                WITH CHECK (EXISTS (
                    SELECT 1 FROM flows WHERE flows.id = flow_nodes.flow_id
                    AND flows.account_id::text = current_setting('app.current_tenant_id', true)
                ));
        ");

        DB::unprepared("GRANT SELECT, INSERT, UPDATE, DELETE ON flow_nodes TO portal_rw");
        DB::unprepared("GRANT SELECT ON flow_nodes TO portal_ro");
        DB::unprepared("GRANT ALL ON flow_nodes TO svc_red");
        DB::unprepared("GRANT ALL ON flow_nodes TO ops_admin");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS flow_nodes_tenant_isolation ON flow_nodes");
        DB::unprepared("ALTER TABLE flow_nodes DISABLE ROW LEVEL SECURITY");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_flow_nodes_uuid ON flow_nodes");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_flow_nodes()");
        Schema::dropIfExists('flow_nodes');
    }
};

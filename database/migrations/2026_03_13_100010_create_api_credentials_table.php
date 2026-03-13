<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('name', 100);
            $table->string('auth_type', 20)->default('bearer');
            $table->text('credentials'); // Encrypted JSON via Laravel Crypt
            $table->text('description')->nullable();
            $table->timestampTz('last_used_at')->nullable();
            $table->uuid('created_by');
            $table->timestampsTz();
            $table->softDeletes();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('created_by')->references('id')->on('users');

            $table->index('account_id');
        });

        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_api_credentials()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;

            CREATE TRIGGER before_insert_api_credentials_uuid
            BEFORE INSERT ON api_credentials
            FOR EACH ROW EXECUTE FUNCTION generate_uuid_api_credentials();
        ");

        DB::unprepared("
            ALTER TABLE api_credentials ENABLE ROW LEVEL SECURITY;
            ALTER TABLE api_credentials FORCE ROW LEVEL SECURITY;

            DROP POLICY IF EXISTS api_credentials_tenant_isolation ON api_credentials;
            CREATE POLICY api_credentials_tenant_isolation ON api_credentials
                USING (account_id::text = current_setting('app.current_tenant_id', true))
                WITH CHECK (account_id::text = current_setting('app.current_tenant_id', true));
        ");

        DB::unprepared("GRANT SELECT, INSERT, UPDATE, DELETE ON api_credentials TO portal_rw");
        DB::unprepared("GRANT SELECT ON api_credentials TO portal_ro");
        DB::unprepared("GRANT ALL ON api_credentials TO svc_red");
        DB::unprepared("GRANT ALL ON api_credentials TO ops_admin");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS api_credentials_tenant_isolation ON api_credentials");
        DB::unprepared("ALTER TABLE api_credentials DISABLE ROW LEVEL SECURITY");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_api_credentials_uuid ON api_credentials");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_api_credentials()");
        Schema::dropIfExists('api_credentials');
    }
};

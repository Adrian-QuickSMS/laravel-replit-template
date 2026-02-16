<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Custom Field Definitions (EAV Schema Layer)
 *
 * DATA CLASSIFICATION: Internal - Tenant Configuration
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * PURPOSE:
 * - Defines the schema for tenant-created custom fields on contacts
 * - Actual values stored in contacts.custom_data (JSONB)
 * - Used for validation, UI rendering, filter type-awareness, template merge
 * - field_type determines available operators in dynamic list rules
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE custom_field_type AS ENUM ('text', 'number', 'date', 'boolean', 'enum')");

        Schema::create('contact_custom_field_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('account_id')->comment('FK to accounts.id — one set of definitions per tenant');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->string('field_name', 100)->comment('Machine-friendly key stored in contacts.custom_data');
            $table->string('field_label', 255)->nullable()->comment('Human-friendly display label');
            $table->text('description')->nullable();

            // enum_options: JSON array for dropdown fields e.g. ["Gold", "Silver", "Bronze"]
            $table->jsonb('enum_options')->nullable()->comment('JSON array of allowed values when field_type = enum');

            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0)->comment('Display order in UI');

            $table->timestamps();

            // One field name per account
            $table->unique(['account_id', 'field_name']);
            $table->index('account_id');
            $table->index(['account_id', 'sort_order']);
        });

        // Add ENUM column after table creation
        DB::statement("ALTER TABLE contact_custom_field_definitions ADD COLUMN field_type custom_field_type DEFAULT 'text'");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_contact_custom_field_defs()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.id IS NULL THEN
                    NEW.id = gen_random_uuid();
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_contact_custom_field_defs_uuid
            BEFORE INSERT ON contact_custom_field_definitions
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_contact_custom_field_defs();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE contact_custom_field_definitions ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE contact_custom_field_definitions FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY custom_field_defs_tenant_isolation ON contact_custom_field_definitions
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY custom_field_defs_postgres_bypass ON contact_custom_field_definitions
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS custom_field_defs_postgres_bypass ON contact_custom_field_definitions");
        DB::unprepared("DROP POLICY IF EXISTS custom_field_defs_tenant_isolation ON contact_custom_field_definitions");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_contact_custom_field_defs_uuid ON contact_custom_field_definitions");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_contact_custom_field_defs()");

        Schema::dropIfExists('contact_custom_field_definitions');

        DB::statement("DROP TYPE IF EXISTS custom_field_type CASCADE");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Core Contacts Table
 *
 * DATA CLASSIFICATION: Confidential - Customer PII
 * SIDE: GREEN (customer portal accessible via scoped queries)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * SECURITY NOTES:
 * - E.164 mobile_number is the primary matching identifier (inbox, opt-out)
 * - Row Level Security enforces account_id filtering (fail-closed)
 * - custom_data JSONB stores tenant-defined fields (schema in contact_custom_field_definitions)
 * - GIN index on custom_data for filter/search performance
 * - sub_account_id nullable — contacts can be scoped to sub-accounts or account-wide
 */
return new class extends Migration
{
    public function up(): void
    {
        // Create ENUM types for contacts
        DB::statement("CREATE TYPE contact_status AS ENUM ('active', 'opted_out')");
        DB::statement("CREATE TYPE contact_source AS ENUM ('ui', 'import', 'api', 'email_to_sms')");

        Schema::create('contacts', function (Blueprint $table) {
            // Primary identifier — Native PostgreSQL UUID
            $table->uuid('id')->primary();

            // MANDATORY tenant isolation
            $table->uuid('account_id')->comment('FK to accounts.id — MANDATORY for all queries');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            // Optional sub-account scoping
            $table->uuid('sub_account_id')->nullable()->comment('FK to sub_accounts.id — optional sub-account filter');
            $table->foreign('sub_account_id')->references('id')->on('sub_accounts')->onDelete('set null');

            // Primary identifier for matching (inbox, opt-out, dedup)
            $table->string('mobile_number', 20)->comment('E.164 format e.g. +447700900000');

            // System default fields — filterable, sortable, template-usable
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('postcode', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable()->comment('ISO 3166-1 alpha-2');

            // Tenant-defined custom fields — JSONB for fast reads, GIN-indexed
            $table->jsonb('custom_data')->default('{}')->comment('Tenant-defined fields; schema in contact_custom_field_definitions');

            // Audit fields
            $table->string('created_by')->nullable()->comment('User or system that created this contact');
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('account_id');
            $table->unique(['account_id', 'mobile_number']); // One contact per number per account
            $table->index(['account_id', 'sub_account_id']);
            $table->index(['account_id', 'email']);
            $table->index(['account_id', 'first_name', 'last_name']);
            $table->index('mobile_number'); // Global lookup for inbound matching
        });

        // Add ENUM columns after table creation (Blueprint doesn't support PostgreSQL enums)
        DB::statement("ALTER TABLE contacts ADD COLUMN status contact_status DEFAULT 'active'");
        DB::statement("ALTER TABLE contacts ADD COLUMN source contact_source DEFAULT 'ui'");
        DB::statement("CREATE INDEX idx_contacts_account_status ON contacts (account_id, status)");
        DB::statement("CREATE INDEX idx_contacts_account_source ON contacts (account_id, source)");
        DB::statement("CREATE INDEX idx_contacts_created_at ON contacts (account_id, created_at)");

        // GIN index on JSONB custom_data for filter/search
        DB::statement("CREATE INDEX idx_contacts_custom_data ON contacts USING GIN (custom_data)");

        // PL/pgSQL trigger function for UUID generation
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_contacts()
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
            CREATE TRIGGER before_insert_contacts_uuid
            BEFORE INSERT ON contacts
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_contacts();
        ");

        // Tenant validation trigger — fail-closed
        DB::unprepared("
            CREATE OR REPLACE FUNCTION validate_contacts_account_id()
            RETURNS TRIGGER AS \$\$
            BEGIN
                IF NEW.account_id IS NULL THEN
                    RAISE EXCEPTION 'account_id is mandatory for all contacts';
                END IF;
                RETURN NEW;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        DB::unprepared("
            CREATE TRIGGER before_insert_contacts_account_validation
            BEFORE INSERT ON contacts
            FOR EACH ROW
            EXECUTE FUNCTION validate_contacts_account_id();
        ");

        // ENABLE ROW LEVEL SECURITY (including for table owner)
        DB::unprepared("ALTER TABLE contacts ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE contacts FORCE ROW LEVEL SECURITY");

        // RLS Policy: Tenant isolation (fail-closed — no tenant context = zero rows)
        DB::unprepared("
            CREATE POLICY contacts_tenant_isolation ON contacts
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        // Postgres superuser bypass (app connects as postgres)
        DB::unprepared("
            CREATE POLICY contacts_postgres_bypass ON contacts
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS contacts_postgres_bypass ON contacts");
        DB::unprepared("DROP POLICY IF EXISTS contacts_tenant_isolation ON contacts");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_contacts_account_validation ON contacts");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_contacts_uuid ON contacts");
        DB::unprepared("DROP FUNCTION IF EXISTS validate_contacts_account_id()");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_contacts()");

        Schema::dropIfExists('contacts');

        DB::statement("DROP TYPE IF EXISTS contact_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS contact_source CASCADE");
    }
};

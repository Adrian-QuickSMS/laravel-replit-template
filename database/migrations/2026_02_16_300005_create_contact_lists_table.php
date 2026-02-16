<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Contact Lists (Static + Dynamic)
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * PURPOSE:
 * - Static lists: manually curated groups (membership in contact_list_member)
 * - Dynamic lists: rule-based segments evaluated at query time (rules stored as JSONB)
 * - contact_count is denormalised for static lists, recomputed for dynamic lists
 * - Dynamic list rules example:
 *   [{"field": "tag", "operator": "contains", "value": "VIP"},
 *    {"field": "status", "operator": "equals", "value": "active"}]
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE list_type AS ENUM ('static', 'dynamic')");

        Schema::create('contact_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('account_id')->comment('FK to accounts.id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->string('name', 255);
            $table->text('description')->nullable();

            // Dynamic list filter rules (null for static lists)
            $table->jsonb('rules')->nullable()->comment('JSON array of filter conditions for dynamic lists');

            // Denormalised count — maintained by app for static, recomputed for dynamic
            $table->integer('contact_count')->default(0);

            // Dynamic list evaluation tracking
            $table->timestamp('last_evaluated')->nullable()->comment('Last time dynamic list rules were evaluated');

            $table->timestamps();

            // One list name per account
            $table->unique(['account_id', 'name']);
            $table->index('account_id');
        });

        // Add ENUM column
        DB::statement("ALTER TABLE contact_lists ADD COLUMN type list_type DEFAULT 'static'");
        DB::statement("CREATE INDEX idx_contact_lists_account_type ON contact_lists (account_id, type)");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_contact_lists()
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
            CREATE TRIGGER before_insert_contact_lists_uuid
            BEFORE INSERT ON contact_lists
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_contact_lists();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE contact_lists ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE contact_lists FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY contact_lists_tenant_isolation ON contact_lists
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY contact_lists_postgres_bypass ON contact_lists
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS contact_lists_postgres_bypass ON contact_lists");
        DB::unprepared("DROP POLICY IF EXISTS contact_lists_tenant_isolation ON contact_lists");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_contact_lists_uuid ON contact_lists");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_contact_lists()");

        Schema::dropIfExists('contact_lists');

        DB::statement("DROP TYPE IF EXISTS list_type CASCADE");
    }
};

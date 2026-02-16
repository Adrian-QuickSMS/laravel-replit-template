<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Contact Book Module — Tags
 *
 * DATA CLASSIFICATION: Internal - Tenant Organisation
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: account_id scoped via RLS + app.current_tenant_id
 *
 * PURPOSE:
 * - Colour-coded labels applied to contacts (many-to-many via contact_tag)
 * - Used for segmentation, filtering, dynamic list rules, campaign targeting
 * - contact_count is denormalised for UI performance (updated by application)
 * - source tracks origin: manual (UI), api, or campaign-assigned
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE tag_source AS ENUM ('manual', 'api', 'campaign')");

        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('account_id')->comment('FK to accounts.id');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

            $table->string('name', 100);
            $table->string('color', 7)->default('#6366f1')->comment('Hex colour code e.g. #6366f1');

            // Denormalised count — updated by app on tag/untag operations
            $table->integer('contact_count')->default(0);
            $table->timestamp('last_used')->nullable()->comment('Last time this tag was applied to a contact');

            $table->timestamps();

            // One tag name per account
            $table->unique(['account_id', 'name']);
            $table->index('account_id');
        });

        // Add ENUM column
        DB::statement("ALTER TABLE tags ADD COLUMN source tag_source DEFAULT 'manual'");

        // UUID generation trigger
        DB::unprepared("
            CREATE OR REPLACE FUNCTION generate_uuid_tags()
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
            CREATE TRIGGER before_insert_tags_uuid
            BEFORE INSERT ON tags
            FOR EACH ROW
            EXECUTE FUNCTION generate_uuid_tags();
        ");

        // Row Level Security
        DB::unprepared("ALTER TABLE tags ENABLE ROW LEVEL SECURITY");
        DB::unprepared("ALTER TABLE tags FORCE ROW LEVEL SECURITY");

        DB::unprepared("
            CREATE POLICY tags_tenant_isolation ON tags
            FOR ALL
            USING (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            )
            WITH CHECK (
                account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::uuid
            );
        ");

        DB::unprepared("
            CREATE POLICY tags_postgres_bypass ON tags
            FOR ALL
            TO postgres
            USING (true)
            WITH CHECK (true);
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP POLICY IF EXISTS tags_postgres_bypass ON tags");
        DB::unprepared("DROP POLICY IF EXISTS tags_tenant_isolation ON tags");
        DB::unprepared("DROP TRIGGER IF EXISTS before_insert_tags_uuid ON tags");
        DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_tags()");

        Schema::dropIfExists('tags');

        DB::statement("DROP TYPE IF EXISTS tag_source CASCADE");
    }
};

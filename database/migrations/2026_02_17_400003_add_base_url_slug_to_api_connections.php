<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE api_connections
            ADD COLUMN IF NOT EXISTS base_url_slug VARCHAR(20) UNIQUE
        ");

        DB::statement("
            UPDATE api_connections
            SET base_url_slug = LOWER(LEFT(REPLACE(id::text, '-', ''), 12))
            WHERE base_url_slug IS NULL
        ");

        DB::statement("
            ALTER TABLE api_connections
            ALTER COLUMN base_url_slug SET NOT NULL
        ");

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS idx_api_connections_base_url_slug
            ON api_connections (base_url_slug)
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS idx_api_connections_base_url_slug");
        DB::statement("ALTER TABLE api_connections DROP COLUMN IF EXISTS base_url_slug");
    }
};

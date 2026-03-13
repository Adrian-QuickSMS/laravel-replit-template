<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE country_requests ALTER COLUMN account_id TYPE uuid USING account_id::text::uuid');
        DB::statement('ALTER TABLE country_requests ALTER COLUMN submitted_by TYPE uuid USING submitted_by::text::uuid');
        DB::statement('ALTER TABLE country_requests ALTER COLUMN reviewed_by TYPE uuid USING reviewed_by::text::uuid');
        DB::statement('ALTER TABLE country_requests ALTER COLUMN sub_account_id TYPE uuid USING sub_account_id::text::uuid');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE country_requests ALTER COLUMN account_id TYPE bigint USING account_id::text::bigint');
        DB::statement('ALTER TABLE country_requests ALTER COLUMN submitted_by TYPE bigint USING submitted_by::text::bigint');
        DB::statement('ALTER TABLE country_requests ALTER COLUMN reviewed_by TYPE bigint USING reviewed_by::text::bigint');
        DB::statement('ALTER TABLE country_requests ALTER COLUMN sub_account_id TYPE bigint USING sub_account_id::text::bigint');
    }
};

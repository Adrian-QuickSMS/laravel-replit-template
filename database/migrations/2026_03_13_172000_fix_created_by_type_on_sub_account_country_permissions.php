<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // created_by stores admin email (varchar), not a UUID
        DB::statement('ALTER TABLE sub_account_country_permissions ALTER COLUMN created_by TYPE varchar(255)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE sub_account_country_permissions ALTER COLUMN created_by TYPE uuid USING created_by::uuid');
    }
};

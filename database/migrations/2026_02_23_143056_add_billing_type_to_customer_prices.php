<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DO $$ BEGIN
            CREATE TYPE billing_charge_type AS ENUM ('per_submitted', 'per_delivered');
        EXCEPTION WHEN duplicate_object THEN NULL;
        END $$");

        DB::statement("ALTER TABLE customer_prices ADD COLUMN IF NOT EXISTS billing_type billing_charge_type NOT NULL DEFAULT 'per_submitted'");

        DB::statement("ALTER TABLE product_tier_prices ADD COLUMN IF NOT EXISTS billing_type billing_charge_type NOT NULL DEFAULT 'per_submitted'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE customer_prices DROP COLUMN IF EXISTS billing_type");
        DB::statement("ALTER TABLE product_tier_prices DROP COLUMN IF EXISTS billing_type");
        DB::statement("DROP TYPE IF EXISTS billing_charge_type");
    }
};

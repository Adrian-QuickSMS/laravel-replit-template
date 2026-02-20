<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create new ENUM types for billing
        DB::statement("CREATE TYPE billing_type AS ENUM ('prepay', 'postpay')");
        DB::statement("CREATE TYPE billing_method AS ENUM ('submitted', 'delivered')");
        DB::statement("CREATE TYPE product_tier AS ENUM ('starter', 'enterprise', 'bespoke')");

        Schema::table('accounts', function (Blueprint $table) {
            $table->uuid('parent_account_id')->nullable()->after('id')
                ->comment('For reseller customers, points to reseller account');
            $table->string('currency', 3)->default('GBP')->after('vat_number');
            $table->decimal('credit_limit', 12, 4)->default(0)->after('currency');
            $table->integer('payment_terms_days')->default(30)->after('credit_limit');
            $table->decimal('platform_fee_monthly', 10, 4)->default(0)->after('payment_terms_days');
            $table->string('xero_contact_id')->nullable()->after('hubspot_company_id');
            $table->string('hubspot_deal_id')->nullable()->after('xero_contact_id');
            $table->string('stripe_customer_id')->nullable()->after('hubspot_deal_id');
            $table->timestamp('trial_expires_at')->nullable()->after('stripe_customer_id');

            $table->foreign('parent_account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->index('parent_account_id');
            $table->index('xero_contact_id');
            $table->index('stripe_customer_id');
        });

        // Add ENUM columns via raw SQL (PostgreSQL)
        DB::statement("ALTER TABLE accounts ADD COLUMN billing_type billing_type DEFAULT 'prepay'");
        DB::statement("ALTER TABLE accounts ADD COLUMN billing_method billing_method DEFAULT 'submitted'");
        DB::statement("ALTER TABLE accounts ADD COLUMN product_tier product_tier DEFAULT 'starter'");
        DB::statement("ALTER TABLE accounts ADD COLUMN account_hierarchy VARCHAR(20) DEFAULT 'direct' CHECK (account_hierarchy IN ('direct', 'reseller', 'reseller_customer'))");

        DB::statement("CREATE INDEX idx_accounts_billing_type ON accounts (billing_type)");
        DB::statement("CREATE INDEX idx_accounts_product_tier ON accounts (product_tier)");
        DB::statement("CREATE INDEX idx_accounts_hierarchy ON accounts (account_hierarchy)");
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn([
                'parent_account_id', 'currency', 'credit_limit',
                'payment_terms_days', 'platform_fee_monthly',
                'xero_contact_id', 'hubspot_deal_id', 'stripe_customer_id',
                'trial_expires_at',
            ]);
        });

        DB::statement("ALTER TABLE accounts DROP COLUMN IF EXISTS billing_type");
        DB::statement("ALTER TABLE accounts DROP COLUMN IF EXISTS billing_method");
        DB::statement("ALTER TABLE accounts DROP COLUMN IF EXISTS product_tier");
        DB::statement("ALTER TABLE accounts DROP COLUMN IF EXISTS account_hierarchy");

        DB::statement("DROP TYPE IF EXISTS billing_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS billing_method CASCADE");
        DB::statement("DROP TYPE IF EXISTS product_tier CASCADE");
    }
};

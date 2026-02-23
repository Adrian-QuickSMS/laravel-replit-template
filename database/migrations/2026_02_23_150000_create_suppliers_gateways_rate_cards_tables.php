<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('suppliers')) {
            DB::statement("
                CREATE TABLE suppliers (
                    id BIGSERIAL PRIMARY KEY,
                    supplier_code VARCHAR(50) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'active',
                    default_currency VARCHAR(3) NOT NULL DEFAULT 'GBP',
                    default_billing_method VARCHAR(20) NOT NULL DEFAULT 'delivered',
                    notes TEXT,
                    contact_name VARCHAR(255),
                    contact_email VARCHAR(255),
                    contact_phone VARCHAR(50),
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    CONSTRAINT suppliers_supplier_code_unique UNIQUE (supplier_code),
                    CONSTRAINT suppliers_status_check CHECK (status IN ('active', 'suspended')),
                    CONSTRAINT suppliers_billing_method_check CHECK (default_billing_method IN ('submitted', 'delivered'))
                )
            ");
            DB::statement("CREATE INDEX suppliers_status_index ON suppliers (status)");
            DB::statement("CREATE INDEX suppliers_supplier_code_index ON suppliers (supplier_code)");
        }

        if (!Schema::hasTable('gateways')) {
            DB::statement("
                CREATE TABLE gateways (
                    id BIGSERIAL PRIMARY KEY,
                    supplier_id BIGINT NOT NULL REFERENCES suppliers(id) ON DELETE CASCADE,
                    gateway_code VARCHAR(50) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    currency VARCHAR(3) NOT NULL DEFAULT 'GBP',
                    billing_method VARCHAR(20) NOT NULL DEFAULT 'delivered',
                    fx_source VARCHAR(255) NOT NULL DEFAULT 'ECB',
                    active BOOLEAN NOT NULL DEFAULT TRUE,
                    last_rate_update TIMESTAMP(0) WITHOUT TIME ZONE,
                    notes TEXT,
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    CONSTRAINT gateways_gateway_code_unique UNIQUE (gateway_code),
                    CONSTRAINT gateways_billing_method_check CHECK (billing_method IN ('submitted', 'delivered'))
                )
            ");
            DB::statement("CREATE INDEX gateways_supplier_id_index ON gateways (supplier_id)");
            DB::statement("CREATE INDEX gateways_gateway_code_index ON gateways (gateway_code)");
            DB::statement("CREATE INDEX gateways_active_index ON gateways (active)");
        }

        if (!Schema::hasTable('rate_cards')) {
            DB::statement("
                CREATE TABLE rate_cards (
                    id BIGSERIAL PRIMARY KEY,
                    supplier_id BIGINT NOT NULL REFERENCES suppliers(id) ON DELETE CASCADE,
                    gateway_id BIGINT NOT NULL REFERENCES gateways(id) ON DELETE CASCADE,
                    mcc_mnc_id BIGINT NOT NULL REFERENCES mcc_mnc_master(id) ON DELETE CASCADE,
                    mcc VARCHAR(3) NOT NULL,
                    mnc VARCHAR(3) NOT NULL,
                    country_name VARCHAR(255) NOT NULL,
                    country_iso VARCHAR(2) NOT NULL,
                    network_name VARCHAR(255) NOT NULL,
                    product_type VARCHAR(20) NOT NULL DEFAULT 'SMS',
                    billing_method VARCHAR(20) NOT NULL,
                    currency VARCHAR(3) NOT NULL,
                    native_rate NUMERIC(10,6) NOT NULL,
                    gbp_rate NUMERIC(10,6) NOT NULL,
                    fx_rate NUMERIC(10,6),
                    fx_timestamp TIMESTAMP(0) WITHOUT TIME ZONE,
                    valid_from DATE NOT NULL,
                    valid_to DATE,
                    active BOOLEAN NOT NULL DEFAULT TRUE,
                    version INTEGER NOT NULL DEFAULT 1,
                    previous_version_id BIGINT REFERENCES rate_cards(id) ON DELETE SET NULL,
                    created_by VARCHAR(255),
                    change_reason VARCHAR(255),
                    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    updated_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
                    CONSTRAINT rate_cards_product_type_check CHECK (product_type IN ('SMS', 'RCS_BASIC', 'RCS_SINGLE')),
                    CONSTRAINT rate_cards_billing_method_check CHECK (billing_method IN ('submitted', 'delivered'))
                )
            ");
            DB::statement("CREATE INDEX rate_cards_lookup_index ON rate_cards (mcc, mnc, gateway_id, product_type, active)");
            DB::statement("CREATE INDEX rate_cards_gateway_active_index ON rate_cards (gateway_id, active, valid_from, valid_to)");
            DB::statement("CREATE INDEX rate_cards_supplier_id_index ON rate_cards (supplier_id)");
            DB::statement("CREATE INDEX rate_cards_valid_from_index ON rate_cards (valid_from)");
            DB::statement("CREATE INDEX rate_cards_valid_to_index ON rate_cards (valid_to)");
            DB::statement("CREATE INDEX rate_cards_active_index ON rate_cards (active)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_cards');
        Schema::dropIfExists('gateways');
        Schema::dropIfExists('suppliers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Campaign balance reservations (portal sends)
        Schema::create('campaign_reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('sub_account_id')->nullable();
            $table->uuid('campaign_id');
            $table->decimal('reserved_amount', 12, 4);
            $table->decimal('used_amount', 12, 4)->default(0);
            $table->decimal('released_amount', 12, 4)->default(0);
            $table->string('status', 20)->default('active')->comment('active, completed, cancelled');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index(['account_id', 'status']);
            $table->index('campaign_id');
        });

        // DLR reconciliation queue (RCS fallback, delivered billing refunds)
        Schema::create('dlr_reconciliation_queue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_log_id');
            $table->uuid('account_id');
            $table->string('original_product_type', 20);
            $table->string('actual_product_type', 20);
            $table->decimal('original_cost', 10, 6);
            $table->decimal('adjusted_cost', 10, 6);
            $table->decimal('adjustment_amount', 10, 6);
            $table->string('adjustment_type', 30)->comment('rcs_to_sms_fallback, delivered_billing_refund');
            $table->string('status', 20)->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->uuid('batch_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index(['status', 'created_at']);
            $table->index('account_id');
            $table->index('batch_id');
        });

        // Recurring monthly charges (virtual numbers, shortcodes, platform fees)
        Schema::create('recurring_charges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('charge_type', 30)->comment('virtual_number, shortcode, platform_fee, support_fee');
            $table->string('description');
            $table->decimal('amount', 10, 4);
            $table->string('currency', 3)->default('GBP');
            $table->string('frequency', 20)->default('monthly');
            $table->date('next_charge_date');
            $table->boolean('active')->default(true);
            $table->string('reference_type', 50)->nullable()->comment('Polymorphic: virtual_number, shortcode');
            $table->uuid('reference_id')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index(['active', 'next_charge_date']);
            $table->index('account_id');
        });

        // Per-message supplier cost tracking (margin analysis)
        Schema::create('supplier_cost_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_log_id');
            $table->uuid('account_id');
            $table->uuid('rate_card_id')->nullable();
            $table->string('country_iso', 2);
            $table->string('mcc', 3)->nullable();
            $table->string('mnc', 3)->nullable();
            $table->uuid('gateway_id')->nullable();
            $table->string('product_type', 20);
            $table->integer('segments')->default(1);
            $table->decimal('customer_price', 10, 6);
            $table->decimal('supplier_cost_native', 10, 6)->default(0);
            $table->decimal('supplier_cost_gbp', 10, 6)->default(0);
            $table->decimal('fx_rate', 10, 6)->default(1);
            $table->decimal('margin_amount', 10, 6)->default(0);
            $table->decimal('margin_percentage', 5, 2)->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index(['account_id', 'created_at']);
            $table->index(['country_iso', 'product_type', 'created_at']);
            $table->index('message_log_id');
        });

        // Balance alert configuration
        Schema::create('balance_alert_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('alert_type', 20)->default('balance_low');
            $table->integer('threshold_percentage');
            $table->boolean('notify_customer')->default(true);
            $table->boolean('notify_admin')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('cooldown_hours')->default(24);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('account_id');
        });

        // Dunning log (overdue invoice reminders)
        Schema::create('dunning_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->uuid('account_id');
            $table->integer('reminder_number')->comment('1, 2, 3, etc.');
            $table->string('action', 50)->comment('email_sent, account_suspended, etc.');
            $table->timestamp('sent_at');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index(['invoice_id', 'reminder_number']);
        });

        // Financial audit log — IMMUTABLE
        Schema::create('financial_audit_log', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actor_id')->nullable();
            $table->string('actor_type', 20)->comment('admin, customer, system, webhook');
            $table->string('action', 100);
            $table->string('entity_type', 50);
            $table->uuid('entity_id')->nullable();
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->inet('ip_address')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index('actor_id');
            $table->index('created_at');
        });

        // Immutability on financial audit log
        DB::unprepared("
            CREATE RULE no_update_financial_audit_log AS ON UPDATE TO financial_audit_log DO INSTEAD NOTHING;
            CREATE RULE no_delete_financial_audit_log AS ON DELETE TO financial_audit_log DO INSTEAD NOTHING;
        ");

        $tables = [
            'campaign_reservations', 'dlr_reconciliation_queue', 'recurring_charges',
            'supplier_cost_log', 'balance_alert_configs', 'dunning_log', 'financial_audit_log'
        ];
        foreach ($tables as $tbl) {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION generate_uuid_{$tbl}()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF NEW.id IS NULL THEN NEW.id = gen_random_uuid(); END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;

                CREATE TRIGGER before_insert_{$tbl}_uuid
                BEFORE INSERT ON {$tbl}
                FOR EACH ROW EXECUTE FUNCTION generate_uuid_{$tbl}();
            ");
        }
    }

    public function down(): void
    {
        DB::unprepared("DROP RULE IF EXISTS no_update_financial_audit_log ON financial_audit_log");
        DB::unprepared("DROP RULE IF EXISTS no_delete_financial_audit_log ON financial_audit_log");

        $tables = [
            'financial_audit_log', 'dunning_log', 'balance_alert_configs',
            'supplier_cost_log', 'recurring_charges', 'dlr_reconciliation_queue',
            'campaign_reservations'
        ];
        foreach ($tables as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
            Schema::dropIfExists($tbl);
        }
    }
};

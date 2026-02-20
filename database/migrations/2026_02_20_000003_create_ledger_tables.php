<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ENUM types
        DB::statement("CREATE TYPE ledger_account_type AS ENUM ('asset', 'liability', 'revenue', 'expense', 'contra_revenue')");
        DB::statement("CREATE TYPE ledger_entry_type AS ENUM (
            'top_up', 'message_charge_prepay', 'message_charge_postpay',
            'supplier_cost', 'rcs_fallback_adjustment', 'delivered_billing_refund',
            'credit_note', 'invoice_payment', 'postpay_advance',
            'platform_fee_prepay', 'platform_fee_postpay',
            'recurring_charge_prepay', 'recurring_charge_postpay',
            'manual_adjustment', 'dd_collection', 'campaign_reservation',
            'campaign_reservation_release'
        )");

        // Chart of Accounts (GL categories)
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('is_system')->default(true)->comment('System accounts cannot be deleted');
            $table->timestamp('created_at')->useCurrent();
        });
        DB::statement("ALTER TABLE ledger_accounts ADD COLUMN account_type ledger_account_type NOT NULL");

        // Journal Headers — IMMUTABLE
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('account_id')->comment('FK to accounts.id (customer account)');
            $table->uuid('sub_account_id')->nullable();
            $table->string('reference_type', 50)->nullable()->comment('Polymorphic: message_log, invoice, stripe_payment');
            $table->uuid('reference_id')->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->decimal('amount', 12, 4)->comment('Absolute transaction value');
            $table->text('description');
            $table->jsonb('metadata')->nullable();
            $table->string('idempotency_key', 255)->unique()->comment('Prevents duplicate ledger entries');
            $table->uuid('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('sub_account_id')->references('id')->on('sub_accounts');

            $table->index('account_id');
            $table->index(['account_id', 'created_at']);
            $table->index('reference_id');
        });
        DB::statement("ALTER TABLE ledger_entries ADD COLUMN entry_type ledger_entry_type NOT NULL");
        DB::statement("CREATE INDEX idx_ledger_entries_type ON ledger_entries (account_id, entry_type)");

        // Journal Lines (debit/credit pairs) — IMMUTABLE
        Schema::create('ledger_lines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('ledger_entry_id');
            $table->string('ledger_account_code', 30);
            $table->decimal('debit', 12, 4)->default(0);
            $table->decimal('credit', 12, 4)->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ledger_entry_id')->references('id')->on('ledger_entries');
            $table->foreign('ledger_account_code')->references('code')->on('ledger_accounts');

            $table->index('ledger_entry_id');
            $table->index('ledger_account_code');
        });

        // Cached balance (denormalized for fast reads, derived from ledger)
        Schema::create('account_balances', function (Blueprint $table) {
            $table->uuid('account_id')->primary();
            $table->string('currency', 3)->default('GBP');
            $table->decimal('balance', 12, 4)->default(0)->comment('Prepay: available funds. Postpay: advance payments.');
            $table->decimal('reserved', 12, 4)->default(0)->comment('Active campaign reservations');
            $table->decimal('credit_limit', 12, 4)->default(0)->comment('Mirror of accounts.credit_limit');
            $table->decimal('effective_available', 12, 4)->default(0)->comment('balance - reserved (prepay) or credit_limit - outstanding + balance (postpay)');
            $table->decimal('total_outstanding', 12, 4)->default(0)->comment('Postpay: unpaid invoice/usage total');
            $table->timestamp('last_reconciled_at')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });

        // IMMUTABILITY: Prevent UPDATE/DELETE on ledger tables
        DB::unprepared("
            CREATE RULE no_update_ledger_entries AS ON UPDATE TO ledger_entries DO INSTEAD NOTHING;
            CREATE RULE no_delete_ledger_entries AS ON DELETE TO ledger_entries DO INSTEAD NOTHING;
            CREATE RULE no_update_ledger_lines AS ON UPDATE TO ledger_lines DO INSTEAD NOTHING;
            CREATE RULE no_delete_ledger_lines AS ON DELETE TO ledger_lines DO INSTEAD NOTHING;
        ");

        // UUID auto-generation triggers
        foreach (['ledger_accounts', 'ledger_entries', 'ledger_lines'] as $tbl) {
            DB::unprepared("
                CREATE OR REPLACE FUNCTION generate_uuid_{$tbl}()
                RETURNS TRIGGER AS \$\$
                BEGIN
                    IF NEW.id IS NULL THEN
                        NEW.id = gen_random_uuid();
                    END IF;
                    RETURN NEW;
                END;
                \$\$ LANGUAGE plpgsql;

                CREATE TRIGGER before_insert_{$tbl}_uuid
                BEFORE INSERT ON {$tbl}
                FOR EACH ROW
                EXECUTE FUNCTION generate_uuid_{$tbl}();
            ");
        }

        // Seed chart of accounts
        $accounts = [
            ['code' => 'CASH', 'name' => 'Cash / Stripe', 'account_type' => 'asset'],
            ['code' => 'AR', 'name' => 'Accounts Receivable', 'account_type' => 'asset'],
            ['code' => 'DEFERRED_REV', 'name' => 'Deferred Revenue', 'account_type' => 'liability'],
            ['code' => 'REVENUE_SMS', 'name' => 'SMS Revenue', 'account_type' => 'revenue'],
            ['code' => 'REVENUE_RCS', 'name' => 'RCS Revenue', 'account_type' => 'revenue'],
            ['code' => 'REVENUE_AI', 'name' => 'AI Query Revenue', 'account_type' => 'revenue'],
            ['code' => 'REVENUE_RECURRING', 'name' => 'Recurring Revenue', 'account_type' => 'revenue'],
            ['code' => 'COGS', 'name' => 'Cost of Goods Sold', 'account_type' => 'expense'],
            ['code' => 'SUPPLIER_PAY', 'name' => 'Supplier Payable', 'account_type' => 'liability'],
            ['code' => 'REFUND', 'name' => 'Refunds & Adjustments', 'account_type' => 'contra_revenue'],
        ];

        foreach ($accounts as $a) {
            DB::table('ledger_accounts')->insert([
                'id' => DB::raw('gen_random_uuid()'),
                'code' => $a['code'],
                'name' => $a['name'],
                'account_type' => $a['account_type'],
                'is_system' => true,
            ]);
        }
    }

    public function down(): void
    {
        DB::unprepared("DROP RULE IF EXISTS no_update_ledger_entries ON ledger_entries");
        DB::unprepared("DROP RULE IF EXISTS no_delete_ledger_entries ON ledger_entries");
        DB::unprepared("DROP RULE IF EXISTS no_update_ledger_lines ON ledger_lines");
        DB::unprepared("DROP RULE IF EXISTS no_delete_ledger_lines ON ledger_lines");

        foreach (['ledger_accounts', 'ledger_entries', 'ledger_lines'] as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
        }

        Schema::dropIfExists('account_balances');
        Schema::dropIfExists('ledger_lines');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('ledger_accounts');

        DB::statement("DROP TYPE IF EXISTS ledger_entry_type CASCADE");
        DB::statement("DROP TYPE IF EXISTS ledger_account_type CASCADE");
    }
};

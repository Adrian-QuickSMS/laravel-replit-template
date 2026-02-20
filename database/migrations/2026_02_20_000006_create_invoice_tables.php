<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE TYPE invoice_type AS ENUM ('usage', 'top_up', 'platform_fee', 'credit_note')");
        DB::statement("CREATE TYPE invoice_status AS ENUM (
            'draft', 'submitted_to_xero', 'issued', 'sent',
            'partially_paid', 'paid', 'overdue', 'void', 'written_off'
        )");
        DB::statement("CREATE TYPE credit_note_status AS ENUM (
            'draft', 'submitted_to_xero', 'issued', 'applied', 'void'
        )");

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number', 30)->unique();
            $table->uuid('account_id');
            $table->string('xero_invoice_id')->nullable()->unique();
            $table->string('xero_invoice_number')->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->decimal('subtotal', 12, 4)->default(0);
            $table->decimal('tax_amount', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->decimal('amount_paid', 12, 4)->default(0);
            $table->decimal('amount_due', 12, 4)->default(0);
            $table->date('issued_date');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->date('billing_period_start')->nullable();
            $table->date('billing_period_end')->nullable();
            $table->integer('payment_terms_days');
            $table->text('notes')->nullable();
            $table->string('xero_pdf_url', 512)->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->index('account_id');
            $table->index(['account_id', 'issued_date']);
            $table->index('due_date');
        });
        DB::statement("ALTER TABLE invoices ADD COLUMN invoice_type invoice_type NOT NULL");
        DB::statement("ALTER TABLE invoices ADD COLUMN status invoice_status NOT NULL DEFAULT 'draft'");
        DB::statement("CREATE INDEX idx_invoices_status ON invoices (status)");
        DB::statement("CREATE INDEX idx_invoices_overdue ON invoices (status, due_date) WHERE status = 'sent' OR status = 'overdue'");

        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->string('country_iso', 2)->nullable();
            $table->string('description');
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 10, 6)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 4)->default(0);
            $table->decimal('line_total', 12, 4)->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->index('invoice_id');
        });
        DB::statement("ALTER TABLE invoice_line_items ADD COLUMN product_type billable_product_type NOT NULL");

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('credit_note_number', 30)->unique();
            $table->uuid('account_id');
            $table->uuid('original_invoice_id')->nullable();
            $table->string('xero_credit_note_id')->nullable()->unique();
            $table->text('reason');
            $table->string('currency', 3)->default('GBP');
            $table->decimal('subtotal', 12, 4)->default(0);
            $table->decimal('tax_amount', 12, 4)->default(0);
            $table->decimal('total', 12, 4)->default(0);
            $table->uuid('applied_to_invoice_id')->nullable();
            $table->date('issued_date');
            $table->uuid('issued_by');
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('original_invoice_id')->references('id')->on('invoices');
            $table->foreign('applied_to_invoice_id')->references('id')->on('invoices');
            $table->index('account_id');
        });
        DB::statement("ALTER TABLE credit_notes ADD COLUMN status credit_note_status NOT NULL DEFAULT 'draft'");

        foreach (['invoices', 'invoice_line_items', 'credit_notes'] as $tbl) {
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
        foreach (['credit_notes', 'invoice_line_items', 'invoices'] as $tbl) {
            DB::unprepared("DROP TRIGGER IF EXISTS before_insert_{$tbl}_uuid ON {$tbl}");
            DB::unprepared("DROP FUNCTION IF EXISTS generate_uuid_{$tbl}()");
            Schema::dropIfExists($tbl);
        }
        DB::statement("DROP TYPE IF EXISTS credit_note_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS invoice_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS invoice_type CASCADE");
    }
};

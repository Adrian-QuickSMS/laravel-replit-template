<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend Accounts Table for Account Activation
 *
 * Adds fields for the 5-section account activation process:
 * 1. Sign Up Details (already exists)
 * 2. Company Information
 * 3. Support & Operations
 * 4. Contract Signatory
 * 5. Billing, VAT and Tax Information
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // =====================================================
            // SECTION 2: COMPANY INFORMATION
            // =====================================================

            // Company Type (mandatory for activation)
            $table->enum('company_type', ['uk_limited', 'sole_trader', 'government_nhs', 'other'])
                ->nullable()
                ->comment('UK Limited, Sole Trader, Government & NHS');

            // Business/Industry Sector (mandatory for activation)
            $table->string('business_sector')->nullable();

            // Website (mandatory for activation, must start with https://)
            $table->string('website', 255)->nullable();

            // Company Registration Number (Companies House - 8 digits for UK Limited)
            $table->string('company_number', 20)->nullable()
                ->comment('Companies House registration number (8 digits for UK Limited)');

            // County/Region (optional, different from country)
            // Note: 'county' field already exists in migration but not in fillable - will add to fillable

            // Operating Address (if different from registered address)
            $table->boolean('operating_address_same_as_registered')->default(true)
                ->comment('Is operating address same as registered address?');
            $table->string('operating_address_line1')->nullable();
            $table->string('operating_address_line2')->nullable();
            $table->string('operating_city', 100)->nullable();
            $table->string('operating_county', 100)->nullable();
            $table->string('operating_postcode', 20)->nullable();
            $table->string('operating_country', 2)->nullable();

            // =====================================================
            // SECTION 3: SUPPORT & OPERATIONS CONTACTS
            // =====================================================

            // Email Contacts (all mandatory)
            $table->string('accounts_billing_email')->nullable()
                ->comment('Accounts & Billing department email');
            $table->string('incident_email')->nullable()
                ->comment('Incident notifications email');

            // Support Contact
            $table->string('support_contact_name', 100)->nullable();
            $table->string('support_contact_email')->nullable();
            $table->string('support_contact_phone', 20)->nullable();

            // Operations Contact
            $table->string('operations_contact_name', 100)->nullable();
            $table->string('operations_contact_email')->nullable();
            $table->string('operations_contact_phone', 20)->nullable();

            // =====================================================
            // SECTION 4: CONTRACT SIGNATORY
            // =====================================================

            // Signatory Details
            $table->string('signatory_name', 100)->nullable()
                ->comment('Full name of authorized signatory');
            $table->string('signatory_title', 100)->nullable()
                ->comment('Job title of signatory');
            $table->string('signatory_email')->nullable();

            // Contract Agreement
            $table->boolean('contract_agreed')->default(false)
                ->comment('Signatory confirmed they are authorized to sign');
            $table->timestamp('contract_signed_at')->nullable();
            $table->string('contract_signed_ip', 45)->nullable();
            $table->string('contract_version', 20)->nullable()
                ->comment('Version of terms signed');

            // =====================================================
            // SECTION 5: BILLING, VAT AND TAX INFORMATION
            // =====================================================

            // Billing Contact (if different from primary)
            $table->string('billing_contact_name', 100)->nullable();
            $table->string('billing_contact_phone', 20)->nullable();

            // Billing Address (if different from registered address)
            $table->boolean('billing_address_same_as_registered')->default(true);
            $table->string('billing_address_line1')->nullable();
            $table->string('billing_address_line2')->nullable();
            $table->string('billing_city', 100)->nullable();
            $table->string('billing_county', 100)->nullable();
            $table->string('billing_postcode', 20)->nullable();
            $table->string('billing_country', 2)->nullable();

            // VAT Information
            $table->boolean('vat_registered')->default(false)
                ->comment('Is company VAT registered?');
            $table->boolean('vat_reverse_charges')->default(false)
                ->comment('VAT reverse charge mechanism applicable?');

            // Tax Information
            $table->string('tax_id', 50)->nullable()
                ->comment('Company Tax Reference / Tax ID');
            $table->string('tax_country', 2)->nullable()
                ->comment('Country of tax registration');

            // Payment Terms
            $table->boolean('purchase_order_required')->default(false)
                ->comment('Does company require PO for invoices?');
            $table->string('purchase_order_number', 100)->nullable()
                ->comment('Purchase order reference number');
            $table->enum('payment_terms', ['immediate', 'net_7', 'net_14', 'net_30', 'net_60'])
                ->default('immediate');

            // =====================================================
            // ACTIVATION STATUS TRACKING
            // =====================================================

            // Track which sections are complete
            $table->boolean('signup_details_complete')->default(false);
            $table->boolean('company_info_complete')->default(false);
            $table->boolean('support_operations_complete')->default(false);
            $table->boolean('contract_signatory_complete')->default(false);
            $table->boolean('billing_vat_complete')->default(false);

            // Overall activation status
            $table->boolean('activation_complete')->default(false);
            $table->timestamp('activated_at')->nullable()
                ->comment('When account completed activation and went live');
            $table->string('activated_by')->nullable()
                ->comment('User ID who completed activation');

            // Indexes for common lookups
            $table->index('company_number');
            $table->index('business_sector');
            $table->index('activation_complete');
            $table->index('vat_registered');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // Remove Section 2: Company Information
            $table->dropColumn([
                'company_type',
                'business_sector',
                'website',
                'company_number',
                'operating_address_same_as_registered',
                'operating_address_line1',
                'operating_address_line2',
                'operating_city',
                'operating_county',
                'operating_postcode',
                'operating_country',
            ]);

            // Remove Section 3: Support & Operations
            $table->dropColumn([
                'accounts_billing_email',
                'incident_email',
                'support_contact_name',
                'support_contact_email',
                'support_contact_phone',
                'operations_contact_name',
                'operations_contact_email',
                'operations_contact_phone',
            ]);

            // Remove Section 4: Contract Signatory
            $table->dropColumn([
                'signatory_name',
                'signatory_title',
                'signatory_email',
                'contract_agreed',
                'contract_signed_at',
                'contract_signed_ip',
                'contract_version',
            ]);

            // Remove Section 5: Billing, VAT and Tax
            $table->dropColumn([
                'billing_contact_name',
                'billing_contact_phone',
                'billing_address_same_as_registered',
                'billing_address_line1',
                'billing_address_line2',
                'billing_city',
                'billing_county',
                'billing_postcode',
                'billing_country',
                'vat_registered',
                'vat_reverse_charges',
                'tax_id',
                'tax_country',
                'purchase_order_required',
                'purchase_order_number',
                'payment_terms',
            ]);

            // Remove activation tracking
            $table->dropColumn([
                'signup_details_complete',
                'company_info_complete',
                'support_operations_complete',
                'contract_signatory_complete',
                'billing_vat_complete',
                'activation_complete',
                'activated_at',
                'activated_by',
            ]);
        });
    }
};

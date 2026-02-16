<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Enforcement Exemptions - unified exemption table for all engines
 *
 * Supports three exemption types:
 * - rule: Exempt from a specific rule (e.g., "Account X exempt from rule SID-001")
 * - engine: Exempt from an entire engine (e.g., "Account X exempt from all SenderID rules")
 * - value: Exempt a specific value (e.g., "Account X can use SenderID 'BARCLAYS'")
 *
 * Supports three scope levels:
 * - global: Platform-wide exemption
 * - account: For a specific account
 * - sub_account: For a specific sub-account
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enforcement_exemptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Which engine this exemption applies to
            $table->string('engine', 20)->comment('senderid, content, url');

            // Exemption type
            $table->string('exemption_type', 20)->comment('rule, engine, value');

            // For type=rule: which specific rule is being exempted
            $table->unsignedBigInteger('rule_id')->nullable()
                ->comment('FK to senderid_rules/content_rules/url_rules.id');
            $table->string('rule_table', 50)->nullable()
                ->comment('Which rule table: senderid_rules, content_rules, url_rules');

            // For type=value: the specific value being exempted
            $table->text('exempted_value')->nullable()
                ->comment('The specific SenderID, content pattern, or URL being exempted');

            // Scope: who this exemption applies to
            $table->string('scope_type', 20)->default('account')
                ->comment('global, account, sub_account');
            $table->uuid('scope_id')->nullable()
                ->comment('account_id or sub_account_id (null for global)');

            $table->text('reason')->nullable()->comment('Why this exemption was granted');
            $table->boolean('is_active')->default(true);

            $table->uuid('created_by')->nullable()->comment('Admin user');
            $table->timestamps();
            $table->softDeletes();

            $table->index('engine');
            $table->index('exemption_type');
            $table->index('scope_type');
            $table->index('scope_id');
            $table->index('is_active');
            $table->index(['engine', 'is_active']);
            $table->index(['engine', 'scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enforcement_exemptions');
    }
};

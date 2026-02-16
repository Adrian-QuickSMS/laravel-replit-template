<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seed default enforcement data:
 * - Normalisation character library (26 letters + digits with known equivalents)
 * - Default SenderID rules (from existing enforcement mock data)
 * - Default Content rules (from existing enforcement mock data)
 * - Default URL rules (from existing enforcement mock data)
 * - System settings (feature flags, anti-spam config, domain age config)
 *
 * This migration populates the enforcement tables with production-ready
 * baseline data extracted from the validated mock dataset.
 *
 * DATA CLASSIFICATION: Internal - Enforcement Configuration
 * SIDE: RED (admin-only)
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ─── Normalisation Characters ───────────────────────────────
        // 26 letters + digits that have known homoglyph equivalents
        $normalisationCharacters = [
            ['base_character' => 'A', 'character_type' => 'letter', 'equivalents' => json_encode(['а', 'ą', 'α', 'ά', 'Α', '4']), 'is_active' => true],
            ['base_character' => 'B', 'character_type' => 'letter', 'equivalents' => json_encode(['ß', 'Β', '8', 'ʙ']), 'is_active' => true],
            ['base_character' => 'C', 'character_type' => 'letter', 'equivalents' => json_encode(['с', 'ç', 'ć', 'ċ', 'Ⅽ']), 'is_active' => true],
            ['base_character' => 'D', 'character_type' => 'letter', 'equivalents' => json_encode(['ԁ', 'ɗ', 'Ⅾ']), 'is_active' => true],
            ['base_character' => 'E', 'character_type' => 'letter', 'equivalents' => json_encode(['е', 'ё', 'ę', 'ě', 'ε', '3']), 'is_active' => true],
            ['base_character' => 'F', 'character_type' => 'letter', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => 'G', 'character_type' => 'letter', 'equivalents' => json_encode(['ɡ', 'ġ', '9']), 'is_active' => true],
            ['base_character' => 'H', 'character_type' => 'letter', 'equivalents' => json_encode(['н', 'Η', 'Н']), 'is_active' => true],
            ['base_character' => 'I', 'character_type' => 'letter', 'equivalents' => json_encode(['і', 'ı', 'ì', 'í', 'î', 'ï', '1', 'l', '|', 'Ι', 'І']), 'is_active' => true],
            ['base_character' => 'J', 'character_type' => 'letter', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => 'K', 'character_type' => 'letter', 'equivalents' => json_encode(['κ', 'Κ', 'к', 'К']), 'is_active' => true],
            ['base_character' => 'L', 'character_type' => 'letter', 'equivalents' => json_encode(['ӏ', 'Ι', 'ℓ', '1', 'Ⅼ']), 'is_active' => true],
            ['base_character' => 'M', 'character_type' => 'letter', 'equivalents' => json_encode(['м', 'Μ', 'М', 'Ⅿ']), 'is_active' => true],
            ['base_character' => 'N', 'character_type' => 'letter', 'equivalents' => json_encode(['и', 'η', 'ñ', 'ń']), 'is_active' => true],
            ['base_character' => 'O', 'character_type' => 'letter', 'equivalents' => json_encode(['о', 'ο', 'ø', 'ö', 'ó', 'ô', 'õ', '0', 'О', 'Ο']), 'is_active' => true],
            ['base_character' => 'P', 'character_type' => 'letter', 'equivalents' => json_encode(['р', 'ρ', 'Ρ', 'Р']), 'is_active' => true],
            ['base_character' => 'Q', 'character_type' => 'letter', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => 'R', 'character_type' => 'letter', 'equivalents' => json_encode(['г', 'ŕ', 'ř']), 'is_active' => true],
            ['base_character' => 'S', 'character_type' => 'letter', 'equivalents' => json_encode(['ѕ', 'ś', 'ş', '$', '5']), 'is_active' => true],
            ['base_character' => 'T', 'character_type' => 'letter', 'equivalents' => json_encode(['т', 'τ', 'ť', 'Τ', 'Т']), 'is_active' => true],
            ['base_character' => 'U', 'character_type' => 'letter', 'equivalents' => json_encode(['υ', 'ü', 'ù', 'ú', 'û', 'μ']), 'is_active' => true],
            ['base_character' => 'V', 'character_type' => 'letter', 'equivalents' => json_encode(['ν', 'Ⅴ']), 'is_active' => true],
            ['base_character' => 'W', 'character_type' => 'letter', 'equivalents' => json_encode(['ω', 'ẃ', 'ẅ']), 'is_active' => true],
            ['base_character' => 'X', 'character_type' => 'letter', 'equivalents' => json_encode(['х', 'χ', 'Χ', 'Х', 'Ⅹ']), 'is_active' => true],
            ['base_character' => 'Y', 'character_type' => 'letter', 'equivalents' => json_encode(['у', 'γ', 'ý', 'ÿ', 'У', 'Υ']), 'is_active' => true],
            ['base_character' => 'Z', 'character_type' => 'letter', 'equivalents' => json_encode(['ź', 'ż', 'ž', '2']), 'is_active' => true],
            ['base_character' => '0', 'character_type' => 'digit', 'equivalents' => json_encode(['о', 'ο', 'О', 'Ο']), 'is_active' => true],
            ['base_character' => '1', 'character_type' => 'digit', 'equivalents' => json_encode(['l', 'I', '|', 'ӏ', 'Ι', 'І']), 'is_active' => true],
            ['base_character' => '2', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => '3', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => '4', 'character_type' => 'digit', 'equivalents' => json_encode(['Ч']), 'is_active' => true],
            ['base_character' => '5', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => '6', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => '7', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => '8', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true],
            ['base_character' => '9', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true],
        ];

        foreach ($normalisationCharacters as $char) {
            DB::table('normalisation_characters')->insert(array_merge($char, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // ─── SenderID Rules ────────────────────────────────────────
        $senderidRules = [
            ['name' => 'Block HMRC Impersonation', 'pattern' => 'HMRC', 'match_type' => 'contains', 'action' => 'block', 'category' => 'government_healthcare', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Block HSBC Impersonation', 'pattern' => 'HSBC', 'match_type' => 'contains', 'action' => 'block', 'category' => 'banking_finance', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Block LLOYDS Impersonation', 'pattern' => 'LLOYDS', 'match_type' => 'contains', 'action' => 'block', 'category' => 'banking_finance', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Block BARCLAYS Impersonation', 'pattern' => 'BARCLAYS', 'match_type' => 'contains', 'action' => 'block', 'category' => 'banking_finance', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Quarantine GOV Pattern', 'pattern' => 'GOV', 'match_type' => 'contains', 'action' => 'quarantine', 'category' => 'government_healthcare', 'use_normalisation' => true, 'priority' => 20],
            ['name' => 'Block BANK Keyword', 'pattern' => 'BANK', 'match_type' => 'contains', 'action' => 'block', 'category' => 'banking_finance', 'use_normalisation' => true, 'priority' => 15],
            ['name' => 'Block NHS Impersonation', 'pattern' => 'NHS', 'match_type' => 'exact', 'action' => 'block', 'category' => 'government_healthcare', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Block DVLA Impersonation', 'pattern' => 'DVLA', 'match_type' => 'exact', 'action' => 'block', 'category' => 'government_healthcare', 'use_normalisation' => true, 'priority' => 10],
        ];

        foreach ($senderidRules as $rule) {
            DB::table('senderid_rules')->insert(array_merge($rule, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // ─── Content Rules ─────────────────────────────────────────
        $contentRules = [
            ['name' => 'Block Urgent Payment Request', 'pattern' => 'urgent.*payment', 'match_type' => 'regex', 'action' => 'block', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Block Account Suspended', 'pattern' => 'account.*suspended', 'match_type' => 'regex', 'action' => 'block', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Quarantine Click Link Urgency', 'pattern' => 'click.*link.*now', 'match_type' => 'regex', 'action' => 'quarantine', 'use_normalisation' => true, 'priority' => 20],
            ['name' => 'Block Verify Identity', 'pattern' => 'verify.*identity', 'match_type' => 'regex', 'action' => 'block', 'use_normalisation' => true, 'priority' => 10],
            ['name' => 'Block Tax Refund Scam', 'pattern' => 'tax.*refund', 'match_type' => 'regex', 'action' => 'block', 'use_normalisation' => true, 'priority' => 10],
        ];

        foreach ($contentRules as $rule) {
            DB::table('content_rules')->insert(array_merge($rule, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // ─── URL Rules ─────────────────────────────────────────────
        $urlRules = [
            ['name' => 'Block Bit.ly Shorteners', 'pattern' => 'bit\\.ly', 'match_type' => 'regex', 'action' => 'block', 'priority' => 10],
            ['name' => 'Block TinyURL Shorteners', 'pattern' => 'tinyurl\\.com', 'match_type' => 'regex', 'action' => 'block', 'priority' => 10],
            ['name' => 'Quarantine Unknown Domains', 'pattern' => '\\.xyz$', 'match_type' => 'regex', 'action' => 'quarantine', 'priority' => 20],
            ['name' => 'Block IP-based URLs', 'pattern' => 'http[s]?://\\d+\\.\\d+\\.\\d+\\.\\d+', 'match_type' => 'regex', 'action' => 'block', 'priority' => 10],
        ];

        foreach ($urlRules as $rule) {
            DB::table('url_rules')->insert(array_merge($rule, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        // ─── System Settings ───────────────────────────────────────
        $settings = [
            // Feature flags
            ['key' => 'enforcement.senderid.enabled', 'value' => json_encode(true), 'group' => 'feature_flags', 'description' => 'Enable SenderID enforcement engine'],
            ['key' => 'enforcement.content.enabled', 'value' => json_encode(true), 'group' => 'feature_flags', 'description' => 'Enable Content enforcement engine'],
            ['key' => 'enforcement.url.enabled', 'value' => json_encode(true), 'group' => 'feature_flags', 'description' => 'Enable URL enforcement engine'],
            ['key' => 'enforcement.normalisation.enabled', 'value' => json_encode(true), 'group' => 'feature_flags', 'description' => 'Enable normalisation globally'],
            ['key' => 'enforcement.quarantine.enabled', 'value' => json_encode(true), 'group' => 'feature_flags', 'description' => 'Enable quarantine workflow (vs immediate block)'],

            // Anti-spam configuration
            ['key' => 'antispam.dedup.enabled', 'value' => json_encode(true), 'group' => 'anti_spam', 'description' => 'Enable duplicate message detection'],
            ['key' => 'antispam.dedup.window_minutes', 'value' => json_encode(60), 'group' => 'anti_spam', 'description' => 'Time window for duplicate detection (minutes)'],
            ['key' => 'antispam.dedup.use_normalisation', 'value' => json_encode(true), 'group' => 'anti_spam', 'description' => 'Apply normalisation before hashing for dedup'],

            // Domain age configuration
            ['key' => 'domain_age.enabled', 'value' => json_encode(true), 'group' => 'domain_age', 'description' => 'Enable domain age checking for URLs'],
            ['key' => 'domain_age.threshold_hours', 'value' => json_encode(72), 'group' => 'domain_age', 'description' => 'Block URLs from domains younger than this (hours)'],
            ['key' => 'domain_age.cache_ttl_hours', 'value' => json_encode(24), 'group' => 'domain_age', 'description' => 'How long to cache WHOIS lookup results (hours)'],
            ['key' => 'domain_age.action', 'value' => json_encode('quarantine'), 'group' => 'domain_age', 'description' => 'Action for young domains: block or quarantine'],

            // Quarantine settings
            ['key' => 'quarantine.expiry_hours', 'value' => json_encode(24), 'group' => 'enforcement', 'description' => 'Hours before quarantined messages auto-expire'],
            ['key' => 'quarantine.require_notes', 'value' => json_encode(true), 'group' => 'enforcement', 'description' => 'Require admin notes when reviewing quarantined messages'],

            // Enforcement pipeline order
            ['key' => 'enforcement.pipeline_order', 'value' => json_encode(['senderid', 'content', 'url']), 'group' => 'enforcement', 'description' => 'Order of enforcement engine evaluation'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->insert(array_merge($setting, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->where('group', 'feature_flags')->delete();
        DB::table('system_settings')->where('group', 'anti_spam')->delete();
        DB::table('system_settings')->where('group', 'domain_age')->delete();
        DB::table('system_settings')->where('group', 'enforcement')->delete();
        DB::table('url_rules')->truncate();
        DB::table('content_rules')->truncate();
        DB::table('senderid_rules')->truncate();
        DB::table('normalisation_characters')->truncate();
    }
};

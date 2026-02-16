<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('senderid_rules')->insert([
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block HMRC Impersonation', 'pattern' => 'HMRC', 'match_type' => 'contains', 'action' => 'block', 'category' => 'government', 'priority' => 10, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block HSBC Impersonation', 'pattern' => 'HSBC', 'match_type' => 'contains', 'action' => 'block', 'category' => 'financial', 'priority' => 20, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block LLOYDS Impersonation', 'pattern' => 'LLOYDS', 'match_type' => 'contains', 'action' => 'block', 'category' => 'financial', 'priority' => 30, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block BARCLAYS Impersonation', 'pattern' => 'BARCLAYS', 'match_type' => 'contains', 'action' => 'block', 'category' => 'financial', 'priority' => 40, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Quarantine GOV Pattern', 'pattern' => 'GOV', 'match_type' => 'contains', 'action' => 'quarantine', 'category' => 'government', 'priority' => 50, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block BANK Keyword', 'pattern' => 'BANK', 'match_type' => 'contains', 'action' => 'block', 'category' => 'financial', 'priority' => 60, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block NHS Impersonation', 'pattern' => 'NHS', 'match_type' => 'exact', 'action' => 'block', 'category' => 'government', 'priority' => 70, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block DVLA Impersonation', 'pattern' => 'DVLA', 'match_type' => 'exact', 'action' => 'block', 'category' => 'government', 'priority' => 80, 'use_normalisation' => true, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('content_rules')->insert([
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block Urgent Payment Request', 'pattern' => 'urgent.*payment', 'match_type' => 'regex', 'action' => 'block', 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block Account Suspended', 'pattern' => 'account.*suspended', 'match_type' => 'regex', 'action' => 'block', 'priority' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Quarantine Click Link Urgency', 'pattern' => 'click.*link.*now', 'match_type' => 'regex', 'action' => 'quarantine', 'priority' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block Verify Identity', 'pattern' => 'verify.*identity', 'match_type' => 'regex', 'action' => 'block', 'priority' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block Tax Refund Scam', 'pattern' => 'tax.*refund', 'match_type' => 'regex', 'action' => 'block', 'priority' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('url_rules')->insert([
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block Bit.ly Shorteners', 'pattern' => 'bit\\.ly', 'match_type' => 'regex', 'action' => 'block', 'priority' => 10, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block TinyURL Shorteners', 'pattern' => 'tinyurl\\.com', 'match_type' => 'regex', 'action' => 'block', 'priority' => 20, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Quarantine Unknown Domains', 'pattern' => '\\.xyz$', 'match_type' => 'regex', 'action' => 'quarantine', 'priority' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['uuid' => DB::raw('gen_random_uuid()'), 'name' => 'Block IP-based URLs', 'pattern' => 'http[s]?://\\d+\\.\\d+\\.\\d+\\.\\d+', 'match_type' => 'regex', 'action' => 'block', 'priority' => 40, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('normalisation_characters')->insert([
            ['base_character' => 'A', 'character_type' => 'letter', 'equivalents' => json_encode(['а', 'ą', 'α', 'ά', 'Α', '4']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'B', 'character_type' => 'letter', 'equivalents' => json_encode(['ß', 'Β', '8', 'ʙ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'C', 'character_type' => 'letter', 'equivalents' => json_encode(['с', 'ç', 'ć', 'ċ', 'Ⅽ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'D', 'character_type' => 'letter', 'equivalents' => json_encode(['ԁ', 'ɗ', 'Ⅾ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'E', 'character_type' => 'letter', 'equivalents' => json_encode(['е', 'ё', 'ę', 'ě', 'ε', '3']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'F', 'character_type' => 'letter', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'G', 'character_type' => 'letter', 'equivalents' => json_encode(['ɡ', 'ġ', '9']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'H', 'character_type' => 'letter', 'equivalents' => json_encode(['н', 'Η', 'Н']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'I', 'character_type' => 'letter', 'equivalents' => json_encode(['і', 'ı', 'ì', 'í', 'î', 'ï', '1', 'l', '|', 'Ι', 'І']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'J', 'character_type' => 'letter', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'K', 'character_type' => 'letter', 'equivalents' => json_encode(['κ', 'Κ', 'к', 'К']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'L', 'character_type' => 'letter', 'equivalents' => json_encode(['ӏ', 'Ι', 'ℓ', '1', 'Ⅼ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'M', 'character_type' => 'letter', 'equivalents' => json_encode(['м', 'Μ', 'М', 'Ⅿ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'N', 'character_type' => 'letter', 'equivalents' => json_encode(['и', 'η', 'ñ', 'ń']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'O', 'character_type' => 'letter', 'equivalents' => json_encode(['о', 'ο', 'ø', 'ö', 'ó', 'ô', 'õ', '0', 'О', 'Ο']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'P', 'character_type' => 'letter', 'equivalents' => json_encode(['р', 'ρ', 'Ρ', 'Р']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'Q', 'character_type' => 'letter', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'R', 'character_type' => 'letter', 'equivalents' => json_encode(['г', 'ŕ', 'ř']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'S', 'character_type' => 'letter', 'equivalents' => json_encode(['ѕ', 'ś', 'ş', '$', '5']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'T', 'character_type' => 'letter', 'equivalents' => json_encode(['т', 'τ', 'ť', 'Τ', 'Т']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'U', 'character_type' => 'letter', 'equivalents' => json_encode(['υ', 'ü', 'ù', 'ú', 'û', 'μ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'V', 'character_type' => 'letter', 'equivalents' => json_encode(['ν', 'Ⅴ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'W', 'character_type' => 'letter', 'equivalents' => json_encode(['ω', 'ẃ', 'ẅ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'X', 'character_type' => 'letter', 'equivalents' => json_encode(['х', 'χ', 'Χ', 'Х', 'Ⅹ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'Y', 'character_type' => 'letter', 'equivalents' => json_encode(['у', 'γ', 'ý', 'ÿ', 'У', 'Υ']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => 'Z', 'character_type' => 'letter', 'equivalents' => json_encode(['ź', 'ż', 'ž', '2']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '0', 'character_type' => 'digit', 'equivalents' => json_encode(['о', 'ο', 'О', 'Ο']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '1', 'character_type' => 'digit', 'equivalents' => json_encode(['l', 'I', '|', 'ӏ', 'Ι', 'І']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '2', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '3', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '4', 'character_type' => 'digit', 'equivalents' => json_encode(['Ч']), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '5', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '6', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '7', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '8', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['base_character' => '9', 'character_type' => 'digit', 'equivalents' => json_encode([]), 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('system_settings')->insert([
            ['setting_key' => 'enforcement.senderid.enabled', 'setting_value' => 'true', 'setting_group' => 'feature_flags', 'description' => 'Enable SenderID enforcement engine', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'enforcement.content.enabled', 'setting_value' => 'true', 'setting_group' => 'feature_flags', 'description' => 'Enable content enforcement engine', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'enforcement.url.enabled', 'setting_value' => 'true', 'setting_group' => 'feature_flags', 'description' => 'Enable URL enforcement engine', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'enforcement.normalisation.enabled', 'setting_value' => 'true', 'setting_group' => 'feature_flags', 'description' => 'Enable normalisation processing', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'enforcement.quarantine.enabled', 'setting_value' => 'true', 'setting_group' => 'feature_flags', 'description' => 'Enable quarantine functionality', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'antispam.dedup.enabled', 'setting_value' => 'false', 'setting_group' => 'anti_spam', 'description' => 'Enable message deduplication', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'antispam.dedup.window_minutes', 'setting_value' => '60', 'setting_group' => 'anti_spam', 'description' => 'Deduplication window in minutes', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'antispam.dedup.use_normalisation', 'setting_value' => 'true', 'setting_group' => 'anti_spam', 'description' => 'Use normalisation for deduplication hashing', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'domain_age.enabled', 'setting_value' => 'false', 'setting_group' => 'domain_age', 'description' => 'Enable domain age checking', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'domain_age.threshold_hours', 'setting_value' => '72', 'setting_group' => 'domain_age', 'description' => 'Minimum domain age in hours', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'domain_age.cache_ttl_hours', 'setting_value' => '24', 'setting_group' => 'domain_age', 'description' => 'Domain age cache TTL in hours', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'domain_age.action', 'setting_value' => 'quarantine', 'setting_group' => 'domain_age', 'description' => 'Action for young domains', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'quarantine.expiry_hours', 'setting_value' => '168', 'setting_group' => 'enforcement', 'description' => 'Quarantine message expiry in hours', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'quarantine.require_notes', 'setting_value' => 'true', 'setting_group' => 'enforcement', 'description' => 'Require reviewer notes for quarantine actions', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'enforcement.pipeline_order', 'setting_value' => 'senderid,content,url', 'setting_group' => 'enforcement', 'description' => 'Order of enforcement engine evaluation', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('setting_group', ['feature_flags', 'anti_spam', 'domain_age', 'enforcement'])->delete();
        DB::table('normalisation_characters')->truncate();
        DB::table('url_rules')->truncate();
        DB::table('content_rules')->truncate();
        DB::table('senderid_rules')->truncate();
    }
};

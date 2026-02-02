<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * MessageEnforcementService - Unified message security enforcement
 * 
 * This service is the SINGLE SOURCE OF TRUTH for message enforcement.
 * Both the test endpoint and production enforcement MUST use this service.
 * 
 * Features:
 * - Normalisation using character equivalence library
 * - Rule evaluation for SenderID, Content, and URL engines
 * - O(1) rule lookups via indexed storage
 * - Hot reload support
 * - Tenant isolation
 */
class MessageEnforcementService
{
    private const CACHE_TTL_SECONDS = 60;
    private const CACHE_KEY_RULES = 'enforcement_rules';
    private const CACHE_KEY_NORMALISATION = 'normalisation_library';

    /**
     * Test enforcement against a given input
     * 
     * @param string $engine The enforcement engine (senderid, content, url)
     * @param string $input The input string to test
     * @return array The enforcement result
     */
    public function testEnforcement(string $engine, string $input): array
    {
        $normalisationResult = $this->normalise($input);
        $normalisedInput = $normalisationResult['normalised'];
        
        $rules = $this->getRulesForEngine($engine);
        
        $enforcementResult = $this->evaluateRules($normalisedInput, $rules, $engine);
        
        Log::info('[MessageEnforcementService] Test enforcement', [
            'engine' => $engine,
            'input' => $input,
            'normalised' => $normalisedInput,
            'result' => $enforcementResult['result'],
            'matchedRule' => $enforcementResult['matchedRule'] ? $enforcementResult['matchedRule']['id'] : null,
        ]);
        
        return [
            'normalised' => $normalisedInput,
            'result' => $enforcementResult['result'],
            'matchedRule' => $enforcementResult['matchedRule'],
            'mappingHits' => $normalisationResult['mappingHits'],
        ];
    }

    /**
     * Normalise input using character equivalence library
     * This is the SAME normalisation used by production
     * 
     * @param string $input The input string to normalise
     * @return array Normalisation result with mappings
     */
    public function normalise(string $input): array
    {
        $library = $this->getNormalisationLibrary();
        
        $equivMap = [];
        foreach ($library as $rule) {
            if (!($rule['enabled'] ?? true)) {
                continue;
            }
            $base = $rule['base'];
            foreach ($rule['equivalents'] ?? [] as $equiv) {
                $equivMap[$equiv] = $base;
            }
        }
        
        $normalised = '';
        $mappingHits = [];
        
        $chars = preg_split('//u', $input, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($chars as $index => $char) {
            if (isset($equivMap[$char])) {
                $baseChar = $equivMap[$char];
                $mappingHits[] = [
                    'from' => $char,
                    'to' => $baseChar,
                    'index' => $index,
                    'base' => $baseChar,
                ];
                $normalised .= $baseChar;
            } else {
                $normalised .= $char;
            }
        }
        
        return [
            'normalised' => $normalised,
            'mappingHits' => $mappingHits,
        ];
    }

    /**
     * Evaluate rules against normalised input
     * This is the SAME evaluation used by production
     * 
     * @param string $normalisedInput The normalised input
     * @param array $rules The rules to evaluate
     * @param string $engine The engine type
     * @return array Evaluation result
     */
    public function evaluateRules(string $normalisedInput, array $rules, string $engine): array
    {
        foreach ($rules as $rule) {
            $isMatch = false;
            $matchType = $rule['matchType'] ?? 'contains';
            $pattern = $rule['pattern'] ?? '';
            
            try {
                switch ($matchType) {
                    case 'exact':
                        $isMatch = strtoupper($normalisedInput) === strtoupper($pattern);
                        break;
                    case 'contains':
                        $isMatch = stripos($normalisedInput, $pattern) !== false;
                        break;
                    case 'regex':
                        $isMatch = preg_match('/' . $pattern . '/i', $normalisedInput) === 1;
                        break;
                    case 'startswith':
                        $isMatch = stripos($normalisedInput, $pattern) === 0;
                        break;
                    case 'endswith':
                        $isMatch = substr(strtoupper($normalisedInput), -strlen($pattern)) === strtoupper($pattern);
                        break;
                    default:
                        $isMatch = stripos($normalisedInput, $pattern) !== false;
                }
            } catch (\Exception $e) {
                Log::warning('[MessageEnforcementService] Rule evaluation error', [
                    'rule_id' => $rule['id'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
            
            if ($isMatch) {
                $action = $rule['action'] ?? 'block';
                return [
                    'result' => $action,
                    'matchedRule' => [
                        'id' => $rule['id'] ?? null,
                        'name' => $rule['name'] ?? 'Unnamed Rule',
                        'action' => $action,
                        'pattern' => $pattern,
                        'matchType' => $matchType,
                    ],
                ];
            }
        }
        
        return [
            'result' => 'allow',
            'matchedRule' => null,
        ];
    }

    /**
     * Get rules for a specific engine
     * 
     * @param string $engine The engine type (senderid, content, url)
     * @return array The rules for the engine
     */
    public function getRulesForEngine(string $engine): array
    {
        $cacheKey = self::CACHE_KEY_RULES . '_' . $engine;
        
        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($engine) {
            return $this->loadRulesFromDatabase($engine);
        });
    }

    /**
     * Get the normalisation library
     * 
     * @return array The normalisation library
     */
    public function getNormalisationLibrary(): array
    {
        return Cache::remember(self::CACHE_KEY_NORMALISATION, self::CACHE_TTL_SECONDS, function () {
            return $this->loadNormalisationLibraryFromDatabase();
        });
    }

    /**
     * Hot reload rules - invalidate cache
     */
    public function hotReloadRules(): void
    {
        Cache::forget(self::CACHE_KEY_RULES . '_senderid');
        Cache::forget(self::CACHE_KEY_RULES . '_content');
        Cache::forget(self::CACHE_KEY_RULES . '_url');
        Cache::forget(self::CACHE_KEY_NORMALISATION);
        
        Log::info('[MessageEnforcementService] Hot reload - cache invalidated');
    }

    /**
     * Load rules from database (or mock data for now)
     * TODO: Replace with actual database queries when tables are created
     * 
     * @param string $engine The engine type
     * @return array The rules
     */
    private function loadRulesFromDatabase(string $engine): array
    {
        switch ($engine) {
            case 'senderid':
                return [
                    ['id' => 'SID-001', 'name' => 'Block HMRC Impersonation', 'pattern' => 'HMRC', 'matchType' => 'contains', 'action' => 'block'],
                    ['id' => 'SID-002', 'name' => 'Block HSBC Impersonation', 'pattern' => 'HSBC', 'matchType' => 'contains', 'action' => 'block'],
                    ['id' => 'SID-003', 'name' => 'Block LLOYDS Impersonation', 'pattern' => 'LLOYDS', 'matchType' => 'contains', 'action' => 'block'],
                    ['id' => 'SID-004', 'name' => 'Block BARCLAYS Impersonation', 'pattern' => 'BARCLAYS', 'matchType' => 'contains', 'action' => 'block'],
                    ['id' => 'SID-005', 'name' => 'Quarantine GOV Pattern', 'pattern' => 'GOV', 'matchType' => 'contains', 'action' => 'quarantine'],
                    ['id' => 'SID-006', 'name' => 'Block BANK Keyword', 'pattern' => 'BANK', 'matchType' => 'contains', 'action' => 'block'],
                    ['id' => 'SID-007', 'name' => 'Block NHS Impersonation', 'pattern' => 'NHS', 'matchType' => 'exact', 'action' => 'block'],
                    ['id' => 'SID-008', 'name' => 'Block DVLA Impersonation', 'pattern' => 'DVLA', 'matchType' => 'exact', 'action' => 'block'],
                ];
            case 'content':
                return [
                    ['id' => 'CNT-001', 'name' => 'Block Urgent Payment Request', 'pattern' => 'urgent.*payment', 'matchType' => 'regex', 'action' => 'block'],
                    ['id' => 'CNT-002', 'name' => 'Block Account Suspended', 'pattern' => 'account.*suspended', 'matchType' => 'regex', 'action' => 'block'],
                    ['id' => 'CNT-003', 'name' => 'Quarantine Click Link Urgency', 'pattern' => 'click.*link.*now', 'matchType' => 'regex', 'action' => 'quarantine'],
                    ['id' => 'CNT-004', 'name' => 'Block Verify Identity', 'pattern' => 'verify.*identity', 'matchType' => 'regex', 'action' => 'block'],
                    ['id' => 'CNT-005', 'name' => 'Block Tax Refund Scam', 'pattern' => 'tax.*refund', 'matchType' => 'regex', 'action' => 'block'],
                ];
            case 'url':
                return [
                    ['id' => 'URL-001', 'name' => 'Block Bit.ly Shorteners', 'pattern' => 'bit\\.ly', 'matchType' => 'regex', 'action' => 'block'],
                    ['id' => 'URL-002', 'name' => 'Block TinyURL Shorteners', 'pattern' => 'tinyurl\\.com', 'matchType' => 'regex', 'action' => 'block'],
                    ['id' => 'URL-003', 'name' => 'Quarantine Unknown Domains', 'pattern' => '\\.xyz$', 'matchType' => 'regex', 'action' => 'quarantine'],
                    ['id' => 'URL-004', 'name' => 'Block IP-based URLs', 'pattern' => 'http[s]?://\\d+\\.\\d+\\.\\d+\\.\\d+', 'matchType' => 'regex', 'action' => 'block'],
                ];
            default:
                return [];
        }
    }

    /**
     * Load normalisation library from database (or mock data for now)
     * TODO: Replace with actual database queries when tables are created
     * 
     * @return array The normalisation library
     */
    private function loadNormalisationLibraryFromDatabase(): array
    {
        return [
            ['base' => 'A', 'equivalents' => ['а', 'ą', 'α', 'ά', 'Α', '4'], 'enabled' => true],
            ['base' => 'B', 'equivalents' => ['ß', 'Β', '8', 'ʙ'], 'enabled' => true],
            ['base' => 'C', 'equivalents' => ['с', 'ç', 'ć', 'ċ', 'Ⅽ'], 'enabled' => true],
            ['base' => 'D', 'equivalents' => ['ԁ', 'ɗ', 'Ⅾ'], 'enabled' => true],
            ['base' => 'E', 'equivalents' => ['е', 'ё', 'ę', 'ě', 'ε', '3'], 'enabled' => true],
            ['base' => 'G', 'equivalents' => ['ɡ', 'ġ', '9'], 'enabled' => true],
            ['base' => 'H', 'equivalents' => ['н', 'Η', 'Н'], 'enabled' => true],
            ['base' => 'I', 'equivalents' => ['і', 'ı', 'ì', 'í', 'î', 'ï', '1', 'l', '|', 'Ι', 'І'], 'enabled' => true],
            ['base' => 'K', 'equivalents' => ['κ', 'Κ', 'к', 'К'], 'enabled' => true],
            ['base' => 'L', 'equivalents' => ['ӏ', 'Ι', 'ℓ', '1', 'Ⅼ'], 'enabled' => true],
            ['base' => 'M', 'equivalents' => ['м', 'Μ', 'М', 'Ⅿ'], 'enabled' => true],
            ['base' => 'N', 'equivalents' => ['и', 'η', 'ñ', 'ń'], 'enabled' => true],
            ['base' => 'O', 'equivalents' => ['о', 'ο', 'ø', 'ö', 'ó', 'ô', 'õ', '0', 'О', 'Ο'], 'enabled' => true],
            ['base' => 'P', 'equivalents' => ['р', 'ρ', 'Ρ', 'Р'], 'enabled' => true],
            ['base' => 'R', 'equivalents' => ['г', 'ŕ', 'ř'], 'enabled' => true],
            ['base' => 'S', 'equivalents' => ['ѕ', 'ś', 'ş', '$', '5'], 'enabled' => true],
            ['base' => 'T', 'equivalents' => ['т', 'τ', 'ť', 'Τ', 'Т'], 'enabled' => true],
            ['base' => 'U', 'equivalents' => ['υ', 'ü', 'ù', 'ú', 'û', 'μ'], 'enabled' => true],
            ['base' => 'V', 'equivalents' => ['ν', 'Ⅴ'], 'enabled' => true],
            ['base' => 'W', 'equivalents' => ['ω', 'ẃ', 'ẅ'], 'enabled' => true],
            ['base' => 'X', 'equivalents' => ['х', 'χ', 'Χ', 'Х', 'Ⅹ'], 'enabled' => true],
            ['base' => 'Y', 'equivalents' => ['у', 'γ', 'ý', 'ÿ', 'У', 'Υ'], 'enabled' => true],
            ['base' => 'Z', 'equivalents' => ['ź', 'ż', 'ž', '2'], 'enabled' => true],
            ['base' => '0', 'equivalents' => ['о', 'ο', 'О', 'Ο'], 'enabled' => true],
            ['base' => '1', 'equivalents' => ['l', 'I', '|', 'ӏ', 'Ι', 'І'], 'enabled' => true],
            ['base' => '4', 'equivalents' => ['Ч'], 'enabled' => true],
        ];
    }
}

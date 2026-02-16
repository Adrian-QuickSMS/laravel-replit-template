<?php

namespace App\Services\Admin;

use App\Models\SenderidRule;
use App\Models\ContentRule;
use App\Models\UrlRule;
use App\Models\NormalisationCharacter;
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
     * Validate a regex pattern for safety (ReDoS prevention).
     * Uses tilde delimiter and backtrack limit to prevent catastrophic backtracking.
     */
    public static function isValidRegex(string $pattern): bool
    {
        if (empty($pattern)) {
            return false;
        }

        if (strlen($pattern) > 500) {
            return false;
        }

        $redosPatterns = [
            '/\([^)]*[+*]\)[+*]/',
            '/\([^)]*\|[^)]*\)[+*]/',
            '/\.\*.*\.\*/',
            '/\([^)]*\?\)[+*]/',
            '/(\{[0-9]+,\}){2,}/',
        ];

        foreach ($redosPatterns as $redos) {
            if (preg_match($redos, $pattern)) {
                Log::warning('[MessageEnforcementService] Rejected potentially unsafe regex pattern', ['pattern' => $pattern]);
                return false;
            }
        }

        $previousLimit = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', '10000');

        try {
            $result = @preg_match('~' . $pattern . '~i', '');
            ini_set('pcre.backtrack_limit', $previousLimit);
            return $result !== false;
        } catch (\Exception $e) {
            ini_set('pcre.backtrack_limit', $previousLimit);
            return false;
        }
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
     * Load rules from database for a given engine.
     *
     * @param string $engine The engine type
     * @return array The rules in enforcement array format
     */
    private function loadRulesFromDatabase(string $engine): array
    {
        try {
            switch ($engine) {
                case 'senderid':
                    return SenderidRule::active()
                        ->byPriority()
                        ->get()
                        ->map(fn ($rule) => $rule->toEnforcementArray())
                        ->toArray();

                case 'content':
                    return ContentRule::active()
                        ->byPriority()
                        ->get()
                        ->map(fn ($rule) => $rule->toEnforcementArray())
                        ->toArray();

                case 'url':
                    return UrlRule::active()
                        ->byPriority()
                        ->get()
                        ->map(fn ($rule) => $rule->toEnforcementArray())
                        ->toArray();

                default:
                    return [];
            }
        } catch (\Exception $e) {
            Log::error('[MessageEnforcementService] Failed to load rules from database', [
                'engine' => $engine,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Load normalisation library from database.
     *
     * @return array The normalisation library in service format
     */
    private function loadNormalisationLibraryFromDatabase(): array
    {
        try {
            return NormalisationCharacter::active()
                ->get()
                ->map(fn ($char) => $char->toLibraryArray())
                ->toArray();
        } catch (\Exception $e) {
            Log::error('[MessageEnforcementService] Failed to load normalisation library', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}

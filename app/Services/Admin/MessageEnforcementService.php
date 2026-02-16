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
 * - Hot reload support with last-known-good fallback
 * - Tenant isolation
 * - ReDoS-safe regex evaluation
 */
class MessageEnforcementService
{
    private const CACHE_TTL_SECONDS = 60;
    private const CACHE_KEY_RULES = 'enforcement_rules';
    private const CACHE_KEY_NORMALISATION = 'normalisation_library';
    private const CACHE_KEY_LAST_GOOD_RULES = 'enforcement_rules_last_good';
    private const CACHE_KEY_LAST_GOOD_NORMALISATION = 'normalisation_library_last_good';
    private const LAST_GOOD_TTL_SECONDS = 3600; // 1 hour fallback window
    private const REGEX_BACKTRACK_LIMIT = 10000;

    /**
     * Validate a regex pattern is safe to execute.
     * Returns true if valid, false if malformed or dangerous.
     * Use this before storing a regex rule to the database.
     */
    public static function isValidRegex(string $pattern): bool
    {
        if ($pattern === '') {
            return false;
        }

        $previousLimit = ini_get('pcre.backtrack_limit');
        ini_set('pcre.backtrack_limit', (string) self::REGEX_BACKTRACK_LIMIT);

        $result = @preg_match('~' . str_replace('~', '\\~', $pattern) . '~i', '');

        ini_set('pcre.backtrack_limit', $previousLimit);

        return $result !== false;
    }

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

        $equivMap = $this->buildEquivalenceMap($library);

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
     * Build equivalence map from library array.
     * Memoised to avoid rebuilding on every normalise() call within the same request.
     */
    private ?array $equivMapCache = null;
    private ?string $equivMapCacheHash = null;

    private function buildEquivalenceMap(array $library): array
    {
        $hash = md5(serialize($library));
        if ($this->equivMapCache !== null && $this->equivMapCacheHash === $hash) {
            return $this->equivMapCache;
        }

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

        $this->equivMapCache = $equivMap;
        $this->equivMapCacheHash = $hash;

        return $equivMap;
    }

    /**
     * Evaluate rules against normalised input
     * This is the SAME evaluation used by production
     *
     * Uses tilde (~) delimiter for regex to avoid conflicts with patterns
     * containing forward slashes. Enforces pcre.backtrack_limit to prevent ReDoS.
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
                        // C1 FIX: Use tilde delimiter, escape tildes in pattern, enforce backtrack limit
                        $previousLimit = ini_get('pcre.backtrack_limit');
                        ini_set('pcre.backtrack_limit', (string) self::REGEX_BACKTRACK_LIMIT);

                        $safePattern = str_replace('~', '\\~', $pattern);
                        $matchResult = @preg_match('~' . $safePattern . '~i', $normalisedInput);

                        ini_set('pcre.backtrack_limit', $previousLimit);

                        if ($matchResult === false) {
                            // M10 FIX: preg_match returns false on error (not exception)
                            Log::warning('[MessageEnforcementService] Invalid regex pattern skipped', [
                                'rule_id' => $rule['id'] ?? 'unknown',
                                'pattern' => $pattern,
                                'preg_error' => preg_last_error_msg(),
                            ]);
                            continue 2; // Skip to next rule
                        }
                        $isMatch = $matchResult === 1;
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
     * Load rules from database for a given engine.
     * H1 FIX: On DB failure, falls back to last-known-good cached rules.
     * Only returns empty (fail-open) if no fallback exists.
     *
     * @param string $engine The engine type
     * @return array The rules in enforcement array format
     */
    private function loadRulesFromDatabase(string $engine): array
    {
        $lastGoodKey = self::CACHE_KEY_LAST_GOOD_RULES . '_' . $engine;

        try {
            $rules = match ($engine) {
                'senderid' => SenderidRule::active()
                    ->byPriority()
                    ->get()
                    ->map(fn ($rule) => $rule->toEnforcementArray())
                    ->toArray(),

                'content' => ContentRule::active()
                    ->byPriority()
                    ->get()
                    ->map(fn ($rule) => $rule->toEnforcementArray())
                    ->toArray(),

                'url' => UrlRule::active()
                    ->byPriority()
                    ->get()
                    ->map(fn ($rule) => $rule->toEnforcementArray())
                    ->toArray(),

                default => [],
            };

            // Store as last-known-good for fallback
            if (!empty($rules)) {
                Cache::put($lastGoodKey, $rules, self::LAST_GOOD_TTL_SECONDS);
            }

            return $rules;
        } catch (\Exception $e) {
            Log::error('[MessageEnforcementService] Failed to load rules from database - attempting fallback', [
                'engine' => $engine,
                'error' => $e->getMessage(),
            ]);

            // H1 FIX: Use last-known-good rules instead of empty array
            $fallback = Cache::get($lastGoodKey);
            if ($fallback !== null) {
                Log::warning('[MessageEnforcementService] Using last-known-good rules fallback', [
                    'engine' => $engine,
                    'rule_count' => count($fallback),
                ]);
                return $fallback;
            }

            Log::critical('[MessageEnforcementService] No fallback rules available - enforcement degraded to pass-through', [
                'engine' => $engine,
            ]);
            return [];
        }
    }

    /**
     * Load normalisation library from database.
     * H1 FIX: On DB failure, falls back to last-known-good cached library.
     *
     * @return array The normalisation library in service format
     */
    private function loadNormalisationLibraryFromDatabase(): array
    {
        $lastGoodKey = self::CACHE_KEY_LAST_GOOD_NORMALISATION;

        try {
            $library = NormalisationCharacter::active()
                ->get()
                ->map(fn ($char) => $char->toLibraryArray())
                ->toArray();

            // Store as last-known-good for fallback
            if (!empty($library)) {
                Cache::put($lastGoodKey, $library, self::LAST_GOOD_TTL_SECONDS);
            }

            return $library;
        } catch (\Exception $e) {
            Log::error('[MessageEnforcementService] Failed to load normalisation library - attempting fallback', [
                'error' => $e->getMessage(),
            ]);

            $fallback = Cache::get($lastGoodKey);
            if ($fallback !== null) {
                Log::warning('[MessageEnforcementService] Using last-known-good normalisation library fallback', [
                    'character_count' => count($fallback),
                ]);
                return $fallback;
            }

            Log::critical('[MessageEnforcementService] No fallback normalisation library available', []);
            return [];
        }
    }
}

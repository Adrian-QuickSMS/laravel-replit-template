<?php

namespace App\Services\Admin;

use App\Models\SenderidRule;
use App\Models\ContentRule;
use App\Models\UrlRule;
use App\Models\NormalisationCharacter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MessageEnforcementService
{
    private const CACHE_TTL_SECONDS = 60;
    private const CACHE_KEY_RULES = 'enforcement_rules';
    private const CACHE_KEY_NORMALISATION = 'normalisation_library';

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

    public function evaluateRules(string $normalisedInput, array $rules, string $engine): array
    {
        foreach ($rules as $rule) {
            $isMatch = false;
            $matchType = $rule['matchType'] ?? 'contains';
            $pattern = $rule['pattern'] ?? '';
            
            try {
                switch ($matchType) {
                    case 'exact':
                    case 'exact_domain':
                        $isMatch = strtoupper($normalisedInput) === strtoupper($pattern);
                        break;
                    case 'contains':
                    case 'keyword':
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
                    case 'wildcard':
                        $regexPattern = str_replace(['\*', '\?'], ['.*', '.'], preg_quote($pattern, '/'));
                        $isMatch = preg_match('/^' . $regexPattern . '$/i', $normalisedInput) === 1;
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

    public function getRulesForEngine(string $engine): array
    {
        $cacheKey = self::CACHE_KEY_RULES . '_' . $engine;
        
        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($engine) {
            return $this->loadRulesFromDatabase($engine);
        });
    }

    public function getNormalisationLibrary(): array
    {
        return Cache::remember(self::CACHE_KEY_NORMALISATION, self::CACHE_TTL_SECONDS, function () {
            return $this->loadNormalisationLibraryFromDatabase();
        });
    }

    public function hotReloadRules(): void
    {
        Cache::forget(self::CACHE_KEY_RULES . '_senderid');
        Cache::forget(self::CACHE_KEY_RULES . '_content');
        Cache::forget(self::CACHE_KEY_RULES . '_url');
        Cache::forget(self::CACHE_KEY_NORMALISATION);
        
        Log::info('[MessageEnforcementService] Hot reload - cache invalidated');
    }

    private function loadRulesFromDatabase(string $engine): array
    {
        try {
            switch ($engine) {
                case 'senderid':
                    return SenderidRule::active()->byPriority()->get()
                        ->map(fn($r) => $r->toEnforcementArray())
                        ->toArray();
                case 'content':
                    return ContentRule::active()->byPriority()->get()
                        ->map(fn($r) => $r->toEnforcementArray())
                        ->toArray();
                case 'url':
                    return UrlRule::active()->byPriority()->get()
                        ->map(fn($r) => $r->toEnforcementArray())
                        ->toArray();
                default:
                    return [];
            }
        } catch (\Exception $e) {
            Log::error('[MessageEnforcementService] Failed to load rules from DB', [
                'engine' => $engine,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function loadNormalisationLibraryFromDatabase(): array
    {
        try {
            return NormalisationCharacter::active()->get()
                ->map(fn($c) => $c->toLibraryArray())
                ->toArray();
        } catch (\Exception $e) {
            Log::error('[MessageEnforcementService] Failed to load normalisation library from DB', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}

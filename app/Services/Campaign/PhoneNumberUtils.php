<?php

namespace App\Services\Campaign;

/**
 * Lightweight phone number utilities for campaign recipient processing.
 *
 * Handles:
 * - E.164 format validation and normalisation
 * - Country ISO detection from international dialling prefix
 * - Basic format validation (length, characters)
 *
 * This is intentionally simple. For full carrier lookup and HLR validation,
 * integrate a dedicated provider via the gateway layer.
 */
class PhoneNumberUtils
{
    /**
     * International dialling prefix -> ISO 3166-1 alpha-2 country code.
     *
     * Ordered longest-prefix-first so +44 matches before +4.
     * Covers all major markets; for ambiguous prefixes (e.g., +1 is US/CA),
     * the most common market is returned.
     */
    private const PREFIX_MAP = [
        // North America
        '1' => 'US',

        // UK & Crown Dependencies
        '447' => 'GB', // UK mobile
        '441' => 'GB',
        '442' => 'GB',
        '443' => 'GB',
        '44' => 'GB',

        // Europe
        '353' => 'IE', // Ireland
        '33' => 'FR',  // France
        '49' => 'DE',  // Germany
        '34' => 'ES',  // Spain
        '39' => 'IT',  // Italy
        '31' => 'NL',  // Netherlands
        '32' => 'BE',  // Belgium
        '41' => 'CH',  // Switzerland
        '43' => 'AT',  // Austria
        '45' => 'DK',  // Denmark
        '46' => 'SE',  // Sweden
        '47' => 'NO',  // Norway
        '48' => 'PL',  // Poland
        '351' => 'PT', // Portugal
        '358' => 'FI', // Finland
        '30' => 'GR',  // Greece
        '36' => 'HU',  // Hungary
        '420' => 'CZ', // Czech Republic
        '421' => 'SK', // Slovakia
        '40' => 'RO',  // Romania
        '359' => 'BG', // Bulgaria
        '385' => 'HR', // Croatia
        '386' => 'SI', // Slovenia
        '370' => 'LT', // Lithuania
        '371' => 'LV', // Latvia
        '372' => 'EE', // Estonia
        '356' => 'MT', // Malta
        '357' => 'CY', // Cyprus
        '352' => 'LU', // Luxembourg
        '354' => 'IS', // Iceland

        // Middle East
        '971' => 'AE', // UAE
        '966' => 'SA', // Saudi Arabia
        '974' => 'QA', // Qatar
        '973' => 'BH', // Bahrain
        '968' => 'OM', // Oman
        '965' => 'KW', // Kuwait
        '972' => 'IL', // Israel
        '90' => 'TR',  // Turkey
        '962' => 'JO', // Jordan
        '961' => 'LB', // Lebanon

        // Asia Pacific
        '91' => 'IN',  // India
        '86' => 'CN',  // China
        '81' => 'JP',  // Japan
        '82' => 'KR',  // South Korea
        '61' => 'AU',  // Australia
        '64' => 'NZ',  // New Zealand
        '65' => 'SG',  // Singapore
        '60' => 'MY',  // Malaysia
        '66' => 'TH',  // Thailand
        '62' => 'ID',  // Indonesia
        '63' => 'PH',  // Philippines
        '84' => 'VN',  // Vietnam
        '852' => 'HK', // Hong Kong
        '886' => 'TW', // Taiwan
        '880' => 'BD', // Bangladesh
        '92' => 'PK',  // Pakistan
        '94' => 'LK',  // Sri Lanka

        // Africa
        '27' => 'ZA',  // South Africa
        '234' => 'NG', // Nigeria
        '254' => 'KE', // Kenya
        '233' => 'GH', // Ghana
        '256' => 'UG', // Uganda
        '255' => 'TZ', // Tanzania
        '20' => 'EG',  // Egypt
        '212' => 'MA', // Morocco
        '216' => 'TN', // Tunisia

        // Americas
        '55' => 'BR',  // Brazil
        '52' => 'MX',  // Mexico
        '54' => 'AR',  // Argentina
        '56' => 'CL',  // Chile
        '57' => 'CO',  // Colombia
        '51' => 'PE',  // Peru
    ];

    /**
     * Validate and normalise a phone number to E.164 format.
     *
     * Accepted inputs:
     * - +447700900000 (already E.164)
     * - 447700900000 (missing +)
     * - 07700900000 (UK local, requires defaultCountry)
     * - 00447700900000 (international dialling prefix)
     *
     * @return array{valid: bool, number: string|null, error: string|null}
     */
    public static function normalise(string $input, string $defaultCountry = 'GB'): array
    {
        // Strip whitespace, dashes, parentheses, dots
        $cleaned = preg_replace('/[\s\-\(\)\.]/', '', trim($input));

        // Must contain only digits and optional leading +
        if (!preg_match('/^\+?[0-9]+$/', $cleaned)) {
            return ['valid' => false, 'number' => null, 'error' => 'Contains invalid characters'];
        }

        // Strip leading + for processing
        $digits = ltrim($cleaned, '+');

        // Handle 00 international prefix
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Handle local number format (no country prefix)
        if (str_starts_with($digits, '0') && strlen($digits) >= 10 && strlen($digits) <= 12) {
            $prefix = self::getCountryPrefix($defaultCountry);
            if ($prefix) {
                $digits = $prefix . substr($digits, 1);
            }
        }

        // Validate length: E.164 allows 7–15 digits
        if (strlen($digits) < 7 || strlen($digits) > 15) {
            return [
                'valid' => false,
                'number' => null,
                'error' => 'Number length invalid (must be 7-15 digits)',
            ];
        }

        $e164 = '+' . $digits;

        return ['valid' => true, 'number' => $e164, 'error' => null];
    }

    /**
     * Detect the ISO country code from an E.164 phone number.
     *
     * Uses longest-prefix matching against the prefix map.
     * Returns null if no prefix matches.
     */
    public static function detectCountry(string $e164Number): ?string
    {
        $digits = ltrim($e164Number, '+');

        // Try longest prefixes first (most specific match)
        for ($len = min(strlen($digits), 4); $len >= 1; $len--) {
            $prefix = substr($digits, 0, $len);
            if (isset(self::PREFIX_MAP[$prefix])) {
                return self::PREFIX_MAP[$prefix];
            }
        }

        return null;
    }

    /**
     * Check if a number appears to be a valid mobile number.
     *
     * This is a basic structural check, NOT an HLR lookup.
     * For real validation, use the gateway's number lookup API.
     */
    public static function isValidMobile(string $e164Number): bool
    {
        $digits = ltrim($e164Number, '+');

        // Must start with a digit and be 7-15 digits
        return preg_match('/^[1-9][0-9]{6,14}$/', $digits) === 1;
    }

    /**
     * Batch validate and normalise an array of phone numbers.
     *
     * @param array $numbers Raw phone number strings
     * @param string $defaultCountry Default country for local numbers
     * @return array{valid: array, invalid: array}
     */
    public static function batchNormalise(array $numbers, string $defaultCountry = 'GB'): array
    {
        $valid = [];
        $invalid = [];

        foreach ($numbers as $number) {
            $result = self::normalise($number, $defaultCountry);
            if ($result['valid']) {
                $valid[] = $result['number'];
            } else {
                $invalid[] = ['number' => $number, 'error' => $result['error']];
            }
        }

        return ['valid' => $valid, 'invalid' => $invalid];
    }

    /**
     * Get the international dialling prefix for a country.
     */
    private static function getCountryPrefix(string $countryIso): ?string
    {
        // Reverse lookup: country -> prefix
        // For countries with multiple entries (e.g., GB -> 44, 447, etc.), use the shortest
        static $reverseMap = null;

        if ($reverseMap === null) {
            $reverseMap = [];
            foreach (self::PREFIX_MAP as $prefix => $country) {
                if (!isset($reverseMap[$country]) || strlen($prefix) < strlen($reverseMap[$country])) {
                    $reverseMap[$country] = $prefix;
                }
            }
        }

        return $reverseMap[strtoupper($countryIso)] ?? null;
    }
}

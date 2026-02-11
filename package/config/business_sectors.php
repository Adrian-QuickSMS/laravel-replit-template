<?php

/**
 * Business Sectors / Industries Configuration
 *
 * Defines the available business sectors for account activation
 * Used in the "Company Information" section dropdown
 */
return [
    /**
     * Available business sectors
     *
     * Format: 'value' => 'Display Name'
     */
    'sectors' => [
        'agriculture' => 'Agriculture & Farming',
        'automotive' => 'Automotive',
        'construction' => 'Construction & Building',
        'education' => 'Education & Training',
        'energy' => 'Energy & Utilities',
        'entertainment' => 'Entertainment & Media',
        'finance' => 'Financial Services',
        'food_beverage' => 'Food & Beverage',
        'government' => 'Government & Public Sector',
        'healthcare' => 'Healthcare & Medical',
        'hospitality' => 'Hospitality & Tourism',
        'insurance' => 'Insurance',
        'legal' => 'Legal Services',
        'logistics' => 'Logistics & Transport',
        'manufacturing' => 'Manufacturing',
        'marketing' => 'Marketing & Advertising',
        'nonprofit' => 'Non-Profit & Charity',
        'real_estate' => 'Real Estate & Property',
        'retail' => 'Retail & E-commerce',
        'technology' => 'Technology & Software',
        'telecommunications' => 'Telecommunications',
        'other' => 'Other',
    ],

    /**
     * Sectors requiring additional compliance checks
     * (for future implementation)
     */
    'high_risk_sectors' => [
        'finance',
        'healthcare',
        'insurance',
        'legal',
        'government',
    ],

    /**
     * Default sector if none specified
     */
    'default' => 'other',
];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Consent Versions
    |--------------------------------------------------------------------------
    |
    | Version numbers for different consent types. These are stored with each
    | consent record for GDPR compliance and audit trail purposes.
    |
    */

    'versions' => [
        'terms' => '1.0',
        'privacy' => '1.0',
        'fraud_prevention' => '1.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Document URLs
    |--------------------------------------------------------------------------
    |
    | Public URLs where users can view the full text of each consent document.
    |
    */

    'urls' => [
        'terms' => env('APP_URL', 'https://quicksms.com') . '/terms',
        'privacy' => env('APP_URL', 'https://quicksms.com') . '/privacy',
        'fraud_prevention' => env('APP_URL', 'https://quicksms.com') . '/fraud-prevention',
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Text Labels
    |--------------------------------------------------------------------------
    |
    | Short text descriptions shown to users during signup for each consent.
    |
    */

    'texts' => [
        'terms' => 'I agree to the Terms of Service',
        'privacy' => 'I agree to the Privacy Policy',
        'fraud_prevention' => 'I consent to fraud prevention and identity validation, and I agree that QuickSMS may share my information with trusted third-party fraud prevention, validation, and messaging partners to protect against abuse.',
        'marketing' => 'Receive updates and offers (optional)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Required Consents
    |--------------------------------------------------------------------------
    |
    | Consents that must be accepted during signup. Marketing is optional.
    |
    */

    'required' => [
        'terms' => true,
        'privacy' => true,
        'fraud_prevention' => true,
        'marketing' => false,
    ],

];

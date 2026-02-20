<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    | The default currency for new accounts. One currency per account,
    | immutable after first financial transaction.
    */
    'default_currency' => env('BILLING_DEFAULT_CURRENCY', 'GBP'),

    /*
    |--------------------------------------------------------------------------
    | Test Credits
    |--------------------------------------------------------------------------
    */
    'test_credits' => [
        'default_award' => (int)env('TEST_CREDITS_DEFAULT', 100),
        'expiry_days' => (int)env('TEST_CREDITS_EXPIRY_DAYS', 30),
        'max_test_numbers' => 10,
        'costs' => [
            'uk_sms' => 1,
            'uk_rcs' => 2,
            'international_sms' => 10,
            'international_rcs' => 20,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Number Prefix
    |--------------------------------------------------------------------------
    */
    'invoice_prefix' => env('INVOICE_PREFIX', 'QS'),
    'credit_note_prefix' => env('CREDIT_NOTE_PREFIX', 'CN'),

    /*
    |--------------------------------------------------------------------------
    | VAT
    |--------------------------------------------------------------------------
    */
    'vat' => [
        'uk_rate' => '20.00',
        'default_rate' => '0.00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Balance Alerts — Default Thresholds
    |--------------------------------------------------------------------------
    | Created automatically for new accounts.
    */
    'default_alert_thresholds' => [75, 90, 95],

    /*
    |--------------------------------------------------------------------------
    | Auto Top-Up Safety
    |--------------------------------------------------------------------------
    */
    'auto_topup' => [
        'max_per_day' => (int)env('AUTO_TOPUP_MAX_PER_DAY', 3),
        'min_amount' => '5.00',
        'max_amount' => '50000.00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Reconciliation
    |--------------------------------------------------------------------------
    */
    'reconciliation' => [
        'dlr_batch_size' => 5000,
        'balance_mismatch_threshold' => '0.01',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dunning (Overdue Invoice Follow-up)
    |--------------------------------------------------------------------------
    */
    'dunning' => [
        'reminder_days' => [7, 14, 21],
        'suspension_day' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Financial Data Retention (HMRC: 6 years + current)
    |--------------------------------------------------------------------------
    */
    'retention_years' => 7,
];

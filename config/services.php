<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'hubspot' => [
        'access_token' => env('HUBSPOT_ACCESS_TOKEN'),
        'api_key' => env('HUBSPOT_API_KEY'),
        'client_secret' => env('HUBSPOT_CLIENT_SECRET'),
        'webhook_secret' => env('HUBSPOT_WEBHOOK_SECRET'),
        'ticket_pipeline_id' => env('HUBSPOT_TICKET_PIPELINE_ID', '0'),
        'ticket_pipeline_stage_id' => env('HUBSPOT_TICKET_PIPELINE_STAGE_ID', '1'),
        'portal_id' => env('HUBSPOT_PORTAL_ID'),
    ],

    'github' => [
        'token' => env('GITHUB_TOKEN'),
        'repo' => env('GITHUB_REPO', 'adrian-quicksms/laravel-replit-template'),
        'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
    ],

    'bug_report' => [
        'enabled' => env('BUG_REPORT_WIDGET_ENABLED', false),
        'http_timeout' => (int) env('BUG_REPORT_HTTP_TIMEOUT', 10),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'fraud_scoring' => [
        'url' => env('FRAUD_SCORING_API_URL'),
        'api_key' => env('FRAUD_SCORING_API_KEY'),
        'auto_approve_threshold' => (int) env('FRAUD_SCORING_AUTO_APPROVE', 30),
        'manual_review_threshold' => (int) env('FRAUD_SCORING_MANUAL_REVIEW', 70),
    ],

    'xero' => [
        'client_id' => env('XERO_CLIENT_ID'),
        'client_secret' => env('XERO_CLIENT_SECRET'),
        'redirect_uri' => env('XERO_REDIRECT_URI'),
        'refresh_token' => env('XERO_REFRESH_TOKEN'),
        'tenant_id' => env('XERO_TENANT_ID'),
        'webhook_key' => env('XERO_WEBHOOK_KEY'),
        'base_url' => env('XERO_BASE_URL', 'https://api.xero.com/api.xro/2.0'),
        'bank_account_code' => env('XERO_BANK_ACCOUNT_CODE', '090'),
    ],

];

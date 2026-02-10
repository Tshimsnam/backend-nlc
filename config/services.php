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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'maxicash' => [
        'merchant_id' => env('MAXICASH_MERCHANT_ID'),
        'merchant_password' => env('MAXICASH_MERCHANT_PASSWORD'),
        'webhook_secret' => env('MAXICASH_WEBHOOK_SECRET'),
        'sandbox' => env('MAXICASH_SANDBOX', true),
        'api_url' => env('MAXICASH_API_URL', 'https://webapi-test.maxicashapp.com'),
        'redirect_base' => env('MAXICASH_REDIRECT_BASE', 'https://api-testbed.maxicashapp.com'),
        'language' => env('MAXICASH_LANGUAGE', 'fr'),
        'success_url' => env('MAXICASH_SUCCESS_URL'),
        'failure_url' => env('MAXICASH_FAILURE_URL'),
        'cancel_url' => env('MAXICASH_CANCEL_URL'),
        'notify_url' => env('MAXICASH_NOTIFY_URL'), // URL du webhook ou page notify
    ],

];

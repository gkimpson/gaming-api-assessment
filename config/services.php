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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'gaming_platforms' => [
        'minecraft' => [
            'api_base' => 'https://api.mojang.com',
            'session_server' => 'https://sessionserver.mojang.com',
            'avatar_base' => 'https://crafatar.com/avatars/',
            'timeout' => 10,
            'supports_username' => true,
            'supports_id' => true,
        ],
        'steam' => [
            'api_base' => 'https://ident.tebex.io/usernameservices/4',
            'timeout' => 10,
            'supports_username' => false,
            'supports_id' => true,
        ],
        'xbl' => [
            'api_base' => 'https://ident.tebex.io/usernameservices/3',
            'timeout' => 10,
            'supports_username' => true,
            'supports_id' => true,
        ],
    ],

];

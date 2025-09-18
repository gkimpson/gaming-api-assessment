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
        'cache_ttl_hours' => (int) env('GAMING_PLATFORM_CACHE_TTL_HOURS', 24),
        'minecraft' => [
            'api_base' => env('MINECRAFT_API_URL', 'https://api.mojang.com'),
            'session_server' => env('MINECRAFT_SESSION_SERVER', 'https://sessionserver.mojang.com'),
            'avatar_base' => env('MINECRAFT_AVATAR_BASE', 'https://crafatar.com/avatars/'),
            'supports_username' => true,
            'supports_id' => true,
            'timeout' => (int) env('MINECRAFT_TIMEOUT', 30),
        ],
        'steam' => [
            'api_base' => env('STEAM_API_URL', 'https://ident.tebex.io/usernameservices/4'),
            'supports_username' => false,
            'supports_id' => true,
            'timeout' => (int) env('STEAM_TIMEOUT', 30),
        ],
        'xbl' => [
            'api_base' => env('XBOX_LIVE_API_URL', 'https://ident.tebex.io/usernameservices/3'),
            'supports_username' => true,
            'supports_id' => true,
            'timeout' => (int) env('XBOX_LIVE_TIMEOUT', 30),
        ],
    ],

];

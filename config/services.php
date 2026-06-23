<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Google Gemini Vision API (for AI disease detection)
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

    // Notify BD SMS (for future OTP integration)
    'notify_bd' => [
        'api_key' => env('NOTIFY_BD_API_KEY'),
        'sender_id' => env('NOTIFY_BD_SENDER_ID'),
    ],

    // OpenWeather (Smart Weather module) — blank key falls back to mock data
    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY'),
    ],

    // Protiddhoni voice (Direct TTS + IVR) — token managed via admin panel (encrypted in DB)
    'protiddhoni' => [
        'url' => env('PROTIDDHONI_API_URL', 'https://dashboard.protiddhoni-bd.com/api/surveys/direct-tts'),
        'token' => env('PROTIDDHONI_API_TOKEN'),
        'sender' => env('PROTIDDHONI_SENDER', '09612254680'),
        'voice' => env('PROTIDDHONI_VOICE', 'female'),
        'language' => env('PROTIDDHONI_LANGUAGE', 'bn'),
    ],
];

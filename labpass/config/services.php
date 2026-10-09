<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'vision' => [
        'driver' => env('VISION_DRIVER', 'mock'),
        'url' => env('VISION_URL', 'http://127.0.0.1:8001'),
    ],

    'llm' => [
        'base_url' => env('LLM_BASE_URL', 'https://api.openai.com/v1'),
        'api_key' => env('LLM_API_KEY', ''),
        'model' => env('LLM_MODEL', 'gpt-3.5-turbo'),
    ],

];

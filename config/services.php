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

    'amocrm' => [
        'client_id' => env('AMOCRM_CLIENT_ID'),
        'client_secret' => env('AMOCRM_CLIENT_SECRET'),
        'redirect_uri' => env('AMOCRM_REDIRECT_URI'),
        'subdomain' => env('AMOCRM_SUBDOMAIN'),
        'lead_30s_field_id' => env('AMOCRM_LEAD_30S_FIELD_ID'),
    ],

];

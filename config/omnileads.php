<?php

declare(strict_types=1);

return [
    'base_url' => env('OMNILEADS_BASE_URL', 'https://api.madtec.ru/api'),
    'api_key' => env('OMNILEADS_API_KEY'),
    'jwt' => env('OMNILEADS_JWT'),
    'timeout' => (int) env('OMNILEADS_TIMEOUT', 30),
    'retry' => [
        'enabled' => (bool) env('OMNILEADS_RETRY_ENABLED', true),
        'times' => (int) env('OMNILEADS_RETRY_TIMES', 3),
        'sleep_ms' => (int) env('OMNILEADS_RETRY_SLEEP_MS', 200),
    ],
    'logger_channel' => env('OMNILEADS_LOG_CHANNEL'),
];

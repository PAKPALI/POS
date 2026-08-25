<?php

return [
    'slow_queries' => [
        'enabled' => env('PERFORMANCE_SLOW_QUERY_LOG', false),
        'threshold_ms' => (float) env('PERFORMANCE_SLOW_QUERY_MS', 300),
        'channel' => env('PERFORMANCE_LOG_CHANNEL', 'performance'),
        'include_sql' => env('PERFORMANCE_LOG_SQL', true),
    ],
];

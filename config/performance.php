<?php

return [
    'slow_queries' => [
        'enabled' => env('PERFORMANCE_SLOW_QUERY_LOG', false),
        'threshold_ms' => (float) env('PERFORMANCE_SLOW_QUERY_MS', 300),
        'channel' => env('PERFORMANCE_LOG_CHANNEL', 'performance'),
        'include_sql' => env('PERFORMANCE_LOG_SQL', true),
    ],

    'pdf_exports' => [
        'products_max_rows' => (int) env('PDF_PRODUCTS_MAX_ROWS', 300),
        'inventories_max_rows' => (int) env('PDF_INVENTORIES_MAX_ROWS', 500),
        'sales_max_rows' => (int) env('PDF_SALES_MAX_ROWS', 100),
    ],
];

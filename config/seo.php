<?php

return [
    /* Indexing is opt-in and the middleware also requires APP_ENV=production. */
    'indexing_enabled' => (bool) env('SEO_INDEXING_ENABLED', false),
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Settings
    |--------------------------------------------------------------------------
    */

    'query_caching' => [
        'enabled' => env('QUERY_CACHE_ENABLED', true),
        'ttl' => env('QUERY_CACHE_TTL', 3600), // 1 hour default
    ],

    'view_caching' => [
        'enabled' => env('VIEW_CACHE_ENABLED', true),
    ],

    'route_caching' => [
        'enabled' => env('ROUTE_CACHE_ENABLED', true),
    ],

    'asset_compression' => [
        'enabled' => env('ASSET_COMPRESSION_ENABLED', true),
        'image_quality' => env('IMAGE_QUALITY', 85),
    ],

    'lazy_loading' => [
        'enabled' => env('LAZY_LOADING_ENABLED', true),
        'pagination_per_page' => env('PAGINATION_PER_PAGE', 15),
    ],

    'database_optimization' => [
        'use_indexes' => true,
        'eager_loading' => true,
        'chunk_size' => env('DB_CHUNK_SIZE', 1000),
    ],
];

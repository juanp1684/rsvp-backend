<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'))),

    'allowed_origins_patterns' => env('CORS_ALLOW_SUBDOMAINS')
        ? array_map(function ($origin) {
            preg_match('#^(https?://)(.+)$#', trim($origin), $m);
            return '#^' . $m[1] . '[a-z0-9-]+\.' . preg_quote($m[2], '#') . '$#';
        }, array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', ''))))
        : [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];

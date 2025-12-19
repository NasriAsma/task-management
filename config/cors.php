<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Allow Angular dev servers explicitly so credentials can be used
    'allowed_origins' => [
        'http://localhost:4200',
        'http://127.0.0.1:4200',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // Expose Authorization header if you read it client-side
    'exposed_headers' => ['Authorization'],

    // Cache preflight responses (in seconds) to reduce CORS latency
    'max_age' => 86400,

    // Enable credentials for cookie-based auth (Sanctum SPA) or if Angular sets withCredentials
    'supports_credentials' => true,

];

<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['*', 'sanctum/csrf-cookie'], // endpoints to allow CORS

    'allowed_methods' => ['*'], // allow all HTTP methods
    'allowed_headers' => ['*'], // allow all headers
    'allowed_origins' => ['*'], // your frontend URL
    'allowed_origins_patterns' => [],
    'exposed_headers' => ['Authorization'], // headers exposed to frontend
    'max_age' => 0,
    'supports_credentials' => true, // set true if using cookies/session
];

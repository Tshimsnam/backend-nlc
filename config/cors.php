<?php

return [

    'paths' => ['api/*', 'login',  'logout', 'sanctum/csrf-cookie'],


    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000', 
        'http://192.168.241.9:8080',
        env('FRONTEND_NLC'), 
        env('FRONTEND_WEBSITE_URL'),
        env('MOBILE_TICKET_URL'),
        env('MOBILE_TICKET_APP'),
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

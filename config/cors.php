<?php

return [

    'paths' => ['api/*', 'login',  'logout', 'sanctum/csrf-cookie'],


    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000', 'http://192.168.178.109:3000'], // <-- ton Next.js en local

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

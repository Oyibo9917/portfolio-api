<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:5173'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];



// return [
//     'paths' => ['api/*', 'sanctum/csrf-cookie'],

//     'allowed_methods' => ['*'], // Allow GET, POST, PUT, DELETE, etc.

//     'allowed_origins' => ['*'], // CRITICAL: Allow ALL origins

//     // The 'allowed_origins_patterns' is usually left empty if 'allowed_origins' is '*'
//     'allowed_origins_patterns' => [], 

//     'allowed_headers' => ['*'], // Allow ALL headers (including Authorization)

//     'exposed_headers' => [],

//     'max_age' => 0,
    
//     // Set to false for API flow (as you are using tokens, not session cookies)
//     'supports_credentials' => false, 
// ];
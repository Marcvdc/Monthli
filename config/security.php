<?php
return [
    'encrypt_api_keys' => true,
    'headers' => [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
    ],
    'rate_limit' => 60,
    'log_without_pii' => true,
];

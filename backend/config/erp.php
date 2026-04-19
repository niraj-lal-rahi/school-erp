<?php

return [
    'jwt' => [
        'issuer' => env('JWT_ISSUER', env('APP_URL', 'school-erp')),
        'secret' => env('JWT_SECRET', env('APP_KEY')),
        'ttl' => (int) env('JWT_TTL', 60),
        'refresh_ttl' => (int) env('JWT_REFRESH_TTL', 10080),
    ],
];

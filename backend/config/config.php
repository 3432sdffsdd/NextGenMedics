<?php
// Application configuration for NextGen Medics API

return [
    'app_name' => 'NextGen Medics API',
    'app_env'  => getenv('APP_ENV') ?: 'development',

    // Database connection
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => getenv('DB_PORT') ?: '3306',
        'name'    => getenv('DB_NAME') ?: 'nextgen_medics',
        'user'    => getenv('DB_USER') ?: 'root',
        'pass'    => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],

    // JWT settings
    'jwt' => [
        'secret'  => getenv('JWT_SECRET') ?: 'change-this-to-a-long-random-secret-key-in-production',
        'issuer'  => 'nextgen-medics',
        'expiry'  => 60 * 60 * 24, // 24 hours in seconds
    ],

    // CORS - allowed origins for the React frontend
    'cors' => [
        'allowed_origins' => [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
        ],
    ],
];

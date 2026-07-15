<?php
// Application configuration for NextGen Medics API

return [
    'app_name' => 'NextGen Medics API',
    'app_env'  => getenv('APP_ENV') ?: 'development',
    // Application timezone — used for schedule status (completed / live / upcoming).
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Karachi',

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
        'secret'          => getenv('JWT_SECRET') ?: 'change-this-to-a-long-random-secret-key-in-production',
        'refresh_secret'  => getenv('JWT_REFRESH_SECRET') ?: 'change-this-to-another-long-random-secret-key',
        'issuer'          => 'nextgen-medics',
        'expiry'          => 60 * 60,           // 1 hour access token
        'refresh_expiry'  => 60 * 60 * 24 * 7,  // 7 days refresh token
    ],

    // Mail settings
    'mail' => [
        'from'      => getenv('MAIL_FROM') ?: 'noreply@nextgenmedics.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'NextGen Medics',
    ],

    // WhatsApp Cloud API (Meta Business)
    'whatsapp' => [
        'enabled'          => filter_var(getenv('WHATSAPP_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'phone_number_id'  => getenv('WHATSAPP_PHONE_NUMBER_ID') ?: '',
        'access_token'     => getenv('WHATSAPP_ACCESS_TOKEN') ?: '',
    ],

    'cron_secret' => getenv('CRON_SECRET') ?: 'ngm-cron-local-dev',

    // AI Learning Assistant. Provider-agnostic: any OpenAI-compatible Chat
    // Completions endpoint works (OpenAI, Azure OpenAI, OpenRouter, Groq,
    // Together, DeepSeek, local Ollama/LM Studio, ...). Swap providers by
    // changing env vars only — no code changes required.
    'ai' => [
        'enabled'     => filter_var($_ENV['AI_ENABLED'] ?? getenv('AI_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN),
        'provider'    => $_ENV['AI_PROVIDER'] ?? getenv('AI_PROVIDER') ?: 'openai',
        'api_key'     => $_ENV['AI_API_KEY'] ?? (getenv('AI_API_KEY') ?: ''),
        'base_url'    => rtrim($_ENV['AI_BASE_URL'] ?? getenv('AI_BASE_URL') ?: 'https://api.openai.com/v1', '/'),
        'model'       => $_ENV['AI_MODEL'] ?? getenv('AI_MODEL') ?: 'gpt-4o-mini',
        'temperature' => (float) ($_ENV['AI_TEMPERATURE'] ?? getenv('AI_TEMPERATURE') ?: 0.4),
        'max_tokens'  => (int) ($_ENV['AI_MAX_TOKENS'] ?? getenv('AI_MAX_TOKENS') ?: 4000),
        'timeout'     => (int) ($_ENV['AI_TIMEOUT'] ?? getenv('AI_TIMEOUT') ?: 120),
        'max_input_chars' => (int) ($_ENV['AI_MAX_INPUT_CHARS'] ?? getenv('AI_MAX_INPUT_CHARS') ?: 6000),
    ],

    // Upload settings
    'uploads' => [
        'base_url' => getenv('UPLOAD_BASE_URL') ?: '/storage/uploads',
    ],

    // CORS - allowed origins for the React frontend
    'cors' => [
        'allowed_origins' => [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://192.168.8.100:5173',
            'http://192.168.18.26:5173',
        ],
    ],
];

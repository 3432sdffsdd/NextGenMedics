<?php
// Application configuration for NextGen Medics API

/**
 * Shared-host safe env reader: prefer $_ENV / $_SERVER (filled from .env),
 * then getenv(). Many hosts ignore putenv()/getenv() for custom vars.
 */
$env = static function (string $key, ?string $default = null): ?string {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }
    if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }
    $g = getenv($key);
    if ($g !== false && $g !== '') {
        return (string) $g;
    }
    return $default;
};

return [
    'app_name' => 'NextGen Medics API',
    'app_env'  => $env('APP_ENV', 'development'),
    // Application timezone — used for schedule status (completed / live / upcoming).
    'timezone' => $env('APP_TIMEZONE', 'Asia/Karachi'),

    // Database connection
    'db' => [
        'host'    => $env('DB_HOST', '127.0.0.1'),
        'port'    => $env('DB_PORT', '3306'),
        'name'    => $env('DB_NAME', 'nextgen_medics'),
        'user'    => $env('DB_USER', 'root'),
        'pass'    => $env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],

    // JWT settings
    'jwt' => [
        'secret'          => $env('JWT_SECRET', 'change-this-to-a-long-random-secret-key-in-production'),
        'refresh_secret'  => $env('JWT_REFRESH_SECRET', 'change-this-to-another-long-random-secret-key'),
        'issuer'          => 'nextgen-medics',
        'expiry'          => 60 * 60,           // 1 hour access token
        'refresh_expiry'  => 60 * 60 * 24 * 7,  // 7 days refresh token
    ],

    // Mail settings
    'mail' => [
        'from'      => $env('MAIL_FROM', 'noreply@nextgenmedics.com'),
        'from_name' => $env('MAIL_FROM_NAME', 'NextGen Medics'),
    ],

    // WhatsApp Cloud API (Meta Business)
    'whatsapp' => [
        'enabled'          => filter_var($env('WHATSAPP_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
        'phone_number_id'  => $env('WHATSAPP_PHONE_NUMBER_ID', '') ?? '',
        'access_token'     => $env('WHATSAPP_ACCESS_TOKEN', '') ?? '',
    ],

    'cron_secret' => $env('CRON_SECRET', 'ngm-cron-local-dev'),

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

    // ── AI Study Planner (Groq) ────────────────────────────────
    // Dedicated keys for the premium FCPS Study Planner module.
    // Never hardcode secrets — read only from environment / .env.
    'groq' => [
        'api_key'     => $_ENV['GROQ_API_KEY'] ?? (getenv('GROQ_API_KEY') ?: ''),
        'base_url'    => rtrim($_ENV['GROQ_BASE_URL'] ?? getenv('GROQ_BASE_URL') ?: 'https://api.groq.com/openai/v1', '/'),
        'model'       => $_ENV['GROQ_MODEL'] ?? getenv('GROQ_MODEL') ?: 'openai/gpt-oss-20b',
        'temperature' => (float) ($_ENV['GROQ_TEMPERATURE'] ?? getenv('GROQ_TEMPERATURE') ?: 0.5),
        'max_tokens'  => (int) ($_ENV['GROQ_MAX_TOKENS'] ?? getenv('GROQ_MAX_TOKENS') ?: 4096),
        'timeout'     => (int) ($_ENV['GROQ_TIMEOUT'] ?? getenv('GROQ_TIMEOUT') ?: 120),
        'max_retries' => (int) ($_ENV['GROQ_MAX_RETRIES'] ?? getenv('GROQ_MAX_RETRIES') ?: 3),
    ],

    // ── AI Generation Engine (Gemini) ──────────────────────────
    // Native Google Gemini options for the staged generation engine.
    // The API key is NEVER hardcoded — it is read from the GEMINI_API_KEY
    // environment variable / User Secrets / backend/.env only.
    // Default model is Gemini 3.5 Flash (2.5 Flash is closed to new API keys).
    // Switch via GEMINI_MODEL without touching business logic.
    'gemini' => [
        'api_key'           => $_ENV['GEMINI_API_KEY'] ?? (getenv('GEMINI_API_KEY') ?: ''),
        'model'             => $_ENV['GEMINI_MODEL'] ?? getenv('GEMINI_MODEL') ?: 'gemini-3.5-flash',
        'base_url'          => rtrim($_ENV['GEMINI_BASE_URL'] ?? getenv('GEMINI_BASE_URL') ?: 'https://generativelanguage.googleapis.com/v1beta', '/'),
        'temperature'       => (float) ($_ENV['GEMINI_TEMPERATURE'] ?? getenv('GEMINI_TEMPERATURE') ?: 0.4),
        'top_p'             => (float) ($_ENV['GEMINI_TOP_P'] ?? getenv('GEMINI_TOP_P') ?: 0.95),
        'top_k'             => (int) ($_ENV['GEMINI_TOP_K'] ?? getenv('GEMINI_TOP_K') ?: 40),
        'max_output_tokens' => (int) ($_ENV['GEMINI_MAX_OUTPUT_TOKENS'] ?? getenv('GEMINI_MAX_OUTPUT_TOKENS') ?: 8192),
        'timeout'           => (int) ($_ENV['GEMINI_TIMEOUT'] ?? getenv('GEMINI_TIMEOUT') ?: 120),
        'max_retries'       => (int) ($_ENV['GEMINI_MAX_RETRIES'] ?? getenv('GEMINI_MAX_RETRIES') ?: 3),
        'max_input_chars'   => (int) ($_ENV['GEMINI_MAX_INPUT_CHARS'] ?? getenv('GEMINI_MAX_INPUT_CHARS') ?: 100000),
        // USD per 1,000,000 tokens — used only for the admin cost estimate.
        // Keyed by model prefix; falls back to the '*' default.
        'pricing' => [
            'gemini-3.5-flash' => ['input' => 0.30, 'output' => 2.50],
            'gemini-3.1-flash' => ['input' => 0.15, 'output' => 0.60],
            'gemini-2.5-flash' => ['input' => 0.30, 'output' => 2.50],
            'gemini-2.5-pro'   => ['input' => 1.25, 'output' => 10.00],
            'gemini-2.0-flash' => ['input' => 0.10, 'output' => 0.40],
            '*'                => ['input' => 0.30, 'output' => 2.50],
        ],
    ],

    // Upload settings
    'uploads' => [
        'base_url' => getenv('UPLOAD_BASE_URL') ?: '/storage/uploads',
    ],

    // CORS - allowed origins for the React frontend
    'cors' => [
        'allowed_origins' => array_values(array_filter(array_unique(array_merge(
            [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                'http://192.168.8.100:5173',
                'http://192.168.18.26:5173',
                'https://nextgenmedics.info',
                'https://www.nextgenmedics.info',
                'http://nextgenmedics.info',
                'http://www.nextgenmedics.info',
            ],
            // Optional comma-separated list in .env: CORS_ORIGINS=https://a.com,https://b.com
            array_map('trim', explode(',', (string) ($env('CORS_ORIGINS', '') ?? '')))
        )))),
    ],
];

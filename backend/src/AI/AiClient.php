<?php
namespace App\AI;

/**
 * Factory that builds the configured AI provider. This is the single place to
 * register additional providers in the future.
 */
class AiClient
{
    /** Build a provider from the app config array (config/config.php → 'ai'). */
    public static function make(array $config): AiProviderInterface
    {
        $provider = $config['provider'] ?? 'openai';

        return match ($provider) {
            // Native Google Gemini uses the full application config so it can
            // read the dedicated 'gemini' block (GEMINI_API_KEY, model, ...).
            'gemini' => self::gemini(),
            // All OpenAI-compatible vendors share one implementation.
            'openai', 'openrouter', 'groq', 'together', 'deepseek', 'azure', 'ollama', 'local'
                => new OpenAiCompatibleProvider($config),
            default => new OpenAiCompatibleProvider($config),
        };
    }

    /** Convenience: build from the application config file. */
    public static function fromAppConfig(): AiProviderInterface
    {
        $config = require __DIR__ . '/../../config/config.php';
        return self::make($config['ai'] ?? []);
    }

    /** Build the native Gemini provider (metered) from the 'gemini' config block. */
    public static function gemini(?array $geminiConfig = null): MeteredAiProviderInterface
    {
        if ($geminiConfig === null) {
            $config = require __DIR__ . '/../../config/config.php';
            $geminiConfig = $config['gemini'] ?? [];
        }
        return new GeminiProvider($geminiConfig);
    }
}

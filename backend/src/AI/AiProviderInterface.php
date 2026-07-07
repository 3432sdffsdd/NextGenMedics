<?php
namespace App\AI;

/**
 * Contract every AI provider must satisfy. Adding a new provider (Anthropic,
 * Gemini, Bedrock, ...) means implementing this interface and registering it
 * in AiClient::make() — no consumer code changes required.
 */
interface AiProviderInterface
{
    /** Whether the provider has the credentials/config it needs to run. */
    public function isConfigured(): bool;

    /** Human-readable provider name (for diagnostics). */
    public function name(): string;

    /**
     * Run a single chat completion and return the assistant's text.
     *
     * @param string $system  System / instruction prompt.
     * @param string $user    User prompt (the actual task + content).
     * @param array  $options Optional overrides: json (bool), temperature (float),
     *                        max_tokens (int).
     *
     * @throws \RuntimeException on transport or API error.
     */
    public function complete(string $system, string $user, array $options = []): string;
}

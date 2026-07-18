<?php
namespace App\AI;

/**
 * An AI provider that also reports usage metadata (tokens, latency, retries)
 * and can estimate cost. The generation engine depends on THIS interface — not
 * a concrete class — so the underlying model (e.g. Gemini 2.5 Flash → 2.5 Pro)
 * can change via configuration without any business-logic change.
 */
interface MeteredAiProviderInterface extends AiProviderInterface
{
    /**
     * Run one completion and return the assistant text together with usage.
     *
     * @param array $options Optional overrides: json (bool), temperature (float),
     *                       top_p (float), top_k (int), max_output_tokens (int),
     *                       timeout (int).
     *
     * @return array{
     *   text:string,
     *   prompt_tokens:int,
     *   completion_tokens:int,
     *   total_tokens:int,
     *   latency_ms:int,
     *   retries:int,
     *   model:string
     * }
     *
     * @throws \RuntimeException on transport or API error.
     */
    public function completeWithMeta(string $system, string $user, array $options = []): array;

    /** Estimated cost in USD for the given token counts, using configured pricing. */
    public function estimateCost(int $promptTokens, int $completionTokens): float;
}

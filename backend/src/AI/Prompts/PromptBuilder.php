<?php
namespace App\AI\Prompts;

/**
 * Builds system + user prompts for a generation stage from lecture text.
 * Never hardcodes medical content — only templates + the uploaded lecture.
 */
class PromptBuilder
{
    public function __construct(private PromptRepository $prompts) {}

    /**
     * @param array $options Optional: count (int), avoid (list of strings), batch_note (string)
     * @return array{system:string,user:string}
     */
    public function build(string $stageKey, string $lectureText, array $options = []): array
    {
        $system = $this->prompts->system();
        $instruction = $this->prompts->forStage($stageKey);

        $count = isset($options['count']) ? (int) $options['count'] : null;
        $countLine = '';
        if ($count !== null && $count > 0) {
            $countLine = "Create exactly {$count} items in this request.\n";
        }

        $avoid = '';
        if (!empty($options['avoid']) && is_array($options['avoid'])) {
            $lines = array_slice(array_values($options['avoid']), -20);
            $avoid = "Do NOT duplicate or paraphrase these existing items:\n- "
                   . implode("\n- ", $lines) . "\n";
        }

        $batchNote = !empty($options['batch_note'])
            ? trim((string) $options['batch_note']) . "\n"
            : '';

        $user = $instruction . "\n"
              . $countLine
              . $avoid
              . $batchNote
              . "\nLECTURE TEXT:\n"
              . $lectureText;

        return ['system' => $system, 'user' => $user];
    }
}

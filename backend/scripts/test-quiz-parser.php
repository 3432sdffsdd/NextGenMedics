<?php
require __DIR__ . '/../vendor/autoload.php';

// Minimal bootstrap without full app
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $file = __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) require $file;
});

use App\Services\QuizWordParserService;

$sample = <<<'TEXT'
Hip Quiz
10 questions
1. A patient presents with a 'waddling' gait. When standing on the left leg, the right side of the pelvis drops. Which nerve is most likely compromised?
💡 Hint: Consider which side's muscles are failing to pull the pelvis up while the opposite foot is off the ground.
A.  Left superior gluteal nerve
B.  Right superior gluteal nerve
C.  Right inferior gluteal nerve
D.  Left inferior gluteal nerve
Answer: A
Rationale: The superior gluteal nerve supplies the gluteus medius and minimus, which are responsible for stabilizing the pelvis on the stance limb.
2. During a physical examination, a patient is asked to rise from a seated position without using their arms. The patient struggles significantly with this task but can walk on level ground normally. Which muscle is most likely weakened?
💡 Hint: Identify the large muscle responsible for 'power' movements rather than just steady walking.
A.  Piriformis
B.  Gluteus maximus
C.  Gluteus medius
D.  Tensor fasciae latae
Answer: B
Rationale: The gluteus maximus is the 'power' muscle used for forceful hip extension required for climbing stairs and rising from a seated position.
TEXT;

$parser = new QuizWordParserService();
$result = $parser->parseText($sample);
echo json_encode($result['summary'], JSON_PRETTY_PRINT) . PHP_EOL;
if ($result['summary']['valid'] < 2) {
    echo "FAIL\n";
    print_r($result['invalid']);
    exit(1);
}
echo "OK\n";

<?php
require __DIR__ . '/../bootstrap.php';

$repo = new App\Repositories\ScheduleRepository();
foreach (['Dr Talha', 'Dr Talha ', 'Dr Sidrah', 'Talha', 'Sidrah', 'Dr. Sajika'] as $name) {
    $id = $repo->findTeacherIdByName($name);
    echo $name . ' => ' . ($id ?? 'null') . PHP_EOL;
}

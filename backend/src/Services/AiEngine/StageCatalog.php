<?php
namespace App\Services\AiEngine;

/**
 * Study Tools stages:
 * extract → summary → mnemonics → flashcards → clinical cases
 */
final class StageCatalog
{
    /** @return list<array{key:string,group:int,title:string,target:int,weight:int}> */
    public static function all(): array
    {
        return [
            ['key' => 'extract',        'group' => 0, 'title' => 'Reading lecture file', 'target' => 0,  'weight' => 10],
            ['key' => 'summary',        'group' => 1, 'title' => 'Summary',              'target' => 0,  'weight' => 20],
            ['key' => 'mnemonics',      'group' => 2, 'title' => 'Mnemonics',            'target' => 0,  'weight' => 20],
            ['key' => 'flashcards',     'group' => 2, 'title' => 'Flashcards',           'target' => 30, 'weight' => 30],
            ['key' => 'clinical_cases', 'group' => 3, 'title' => 'Clinical Cases',       'target' => 5,  'weight' => 20],
        ];
    }

    public static function totalWeight(): int
    {
        return array_sum(array_column(self::all(), 'weight'));
    }

    public static function find(string $key): ?array
    {
        foreach (self::all() as $s) {
            if ($s['key'] === $key) {
                return $s;
            }
        }
        return null;
    }

    public static function progressPercent(array $stages): int
    {
        $doneWeight = 0;
        $total = max(1, self::totalWeight());
        $byKey = [];
        foreach ($stages as $row) {
            $byKey[$row['stage_key']] = $row;
        }
        foreach (self::all() as $def) {
            $row = $byKey[$def['key']] ?? null;
            if (!$row) {
                continue;
            }
            $status = $row['status'] ?? 'pending';
            if ($status === 'completed' || $status === 'skipped') {
                $doneWeight += $def['weight'];
            } elseif ($status === 'running' && ($def['target'] ?? 0) > 0) {
                $target = max(1, (int) ($row['target'] ?: $def['target']));
                $done = min($target, (int) ($row['done'] ?? 0));
                $doneWeight += (int) round($def['weight'] * ($done / $target));
            }
        }
        return min(100, (int) round(100 * $doneWeight / $total));
    }
}

<?php
namespace App\Helpers;

class SlugHelper
{
    public static function make(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function unique(string $text, callable $existsCheck): string
    {
        $base = self::make($text);
        $slug = $base;
        $counter = 1;
        while ($existsCheck($slug)) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }
}

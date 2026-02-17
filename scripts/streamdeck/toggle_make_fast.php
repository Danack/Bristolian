#!/usr/bin/env php
<?php

/**
 * Switch phpunit.xml to fast mode: no HTML coverage, skip DB tests.
 * - toggle_slow blocks: content removed (e.g. HTML report off)
 * - toggle_fast blocks: content restored (e.g. exclude db group)
 * Idempotent: run multiple times → stays in fast mode.
 * Usage: php toggle_make_fast.php [path]
 * Default path: phpunit.xml in project root (parent of scripts/).
 */

$path = $argv[1] ?? '/var/app/phpunit.xml';
$text = file_get_contents($path);
if ($text === false) {
    fwrite(STDERR, "Cannot read: {$path}\n");
    exit(1);
}

// Remove content in toggle_slow blocks
$patternSlowRemove = '%'
    . '(\s*)'
    . '(<!--\s*toggle_slow begin\s+.*?\s*-->)'
    . '\s*'
    . '([\s\S]*?)'
    . '(\s*)'
    . '(<!--\s*toggle_slow end\s*-->)'
    . '%';
$text = preg_replace_callback($patternSlowRemove, function (array $m): string {
    return $m[1] . $m[2] . "\n" . $m[4] . $m[5];
}, $text);

// Restore content in toggle_fast blocks (from the start comment)
$patternFastRestore = '%'
    . '(\s*)'
    . '(<!--\s*toggle_fast begin\s+(.*?)\s*-->)'
    . '[\s]*'
    . '(\s*)'
    . '(<!--\s*toggle_fast end\s*-->)'
    . '%';
$text = preg_replace_callback($patternFastRestore, function (array $m): string {
    $content = trim($m[3]);
    return $m[1] . $m[2] . "\n" . $m[4] . $content . "\n" . $m[4] . $m[5];
}, $text);

if ($text === null) {
    fwrite(STDERR, "Regex error\n");
    exit(1);
}

file_put_contents($path, $text);

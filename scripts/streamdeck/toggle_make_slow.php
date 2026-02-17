#!/usr/bin/env php
<?php

/**
 * Switch phpunit.xml to slow/full mode: HTML coverage report, run DB tests.
 * - toggle_fast blocks: content removed (e.g. don't exclude db group)
 * - toggle_slow blocks: content restored (e.g. HTML report on)
 * Idempotent: run multiple times → stays in slow mode.
 * Usage: php toggle_make_slow.php [path]
 * Default path: phpunit.xml in project root (parent of scripts/).
 */

$path = $argv[1] ?? '/var/app/phpunit.xml';
$text = file_get_contents($path);
if ($text === false) {
    fwrite(STDERR, "Cannot read: {$path}\n");
    exit(1);
}

// Remove content in toggle_fast blocks
$patternFastRemove = '%'
    . '(\s*)'
    . '(<!--\s*toggle_fast begin\s+.*?\s*-->)'
    . '\s*'
    . '([\s\S]*?)'
    . '(\s*)'
    . '(<!--\s*toggle_fast end\s*-->)'
    . '%';
$text = preg_replace_callback($patternFastRemove, function (array $m): string {
    return $m[1] . $m[2] . "\n" . $m[4] . $m[5];
}, $text);

// Restore content in toggle_slow blocks (from the start comment)
$patternSlowRestore = '%'
    . '(\s*)'
    . '(<!--\s*toggle_slow begin\s+(.*?)\s*-->)'
    . '[\s]*'
    . '(\s*)'
    . '(<!--\s*toggle_slow end\s*-->)'
    . '%';
$text = preg_replace_callback($patternSlowRestore, function (array $m): string {
    $content = trim($m[3]);
    return $m[1] . $m[2] . "\n" . $m[4] . $content . "\n" . $m[4] . $m[5];
}, $text);

if ($text === null) {
    fwrite(STDERR, "Regex error\n");
    exit(1);
}

file_put_contents($path, $text);

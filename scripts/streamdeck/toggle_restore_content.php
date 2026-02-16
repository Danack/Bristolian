#!/usr/bin/env php
<?php

/**
 * Restore content between "toggle begin" and "toggle end" from the start comment.
 * Idempotent: run multiple times → content stays present.
 * Usage: php toggle_restore_content.php [path]
 * Default path: phpunit.xml in project root (parent of scripts/).
 */

$path = $argv[1] ?? '/var/app/phpunit.xml';
$text = file_get_contents($path);
if ($text === false) {
    fwrite(STDERR, "Cannot read: {$path}\n");
    exit(1);
}

$pattern = '%'
    . '(\s*)'                                    // indent of start line
    . '(<!--\s*toggle begin\s+(.*?)\s*-->)'      // start comment; $3 = content to insert
    . '[\s]*'                                    // only whitespace/newlines between (empty block)
    . '(\s*)'                                    // indent of line before end (used for content line)
    . '(<!--\s*toggle end\s*-->)'                // end comment
    . '%';

$result = preg_replace_callback($pattern, function (array $m): string {
    $indentStart = $m[1];
    $startComment = $m[2];
    $content = trim($m[3]);
    $indentBetween = $m[4];
    $endComment = $m[5];
    return $indentStart . $startComment . "\n" . $indentBetween . $content . "\n" . $indentBetween . $endComment;
}, $text);

if ($result === null) {
    fwrite(STDERR, "Regex error\n");
    exit(1);
}

if ($result === $text) {
    exit(0);
}

file_put_contents($path, $result);

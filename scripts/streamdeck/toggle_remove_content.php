#!/usr/bin/env php
<?php

/**
 * Remove content between "toggle begin" and "toggle end" comments.
 * Idempotent: run multiple times → content stays removed.
 * Usage: php toggle_remove_content.php [path]
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
    . '(<!--\s*toggle begin\s+.*?\s*-->)'        // start comment (content stored here)
    . '\s*'                                      // optional space after comment
    . '([\s\S]*?)'                               // content between (to be removed)
    . '(\s*)'                                    // indent/newline before end
    . '(<!--\s*toggle end\s*-->)'                // end comment
    . '%';

$result = preg_replace_callback($pattern, function (array $m): string {
    $indentStart = $m[1];
    $startComment = $m[2];
    $indentEnd = $m[4];
    $endComment = $m[5];
    return $indentStart . $startComment . "\n" . $indentEnd . $endComment;
}, $text);

if ($result === null) {
    fwrite(STDERR, "Regex error\n");
    exit(1);
}

if ($result === $text) {
    exit(0);
}

file_put_contents($path, $result);

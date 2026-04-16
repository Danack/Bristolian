<?php


/**
 * Usage:
 *   php list_uncovered_lines.php clover.xml [mode]
 *
 * Output:
 *   default: path/to/file.php:LINE
 *   improve_test_coverage: /improve_test_coverage path/to/file.php
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php list_uncovered_lines.php clover.xml [mode]\n");
    exit(1);
}

$cloverFile = $argv[1];
$mode = $argv[2] ?? 'default';

if (!file_exists($cloverFile)) {
    fwrite(STDERR, "File not found: $cloverFile\n");
    exit(1);
}

if ($mode !== 'default' && $mode !== 'improve_test_coverage') {
    fwrite(STDERR, "Unknown mode: $mode\n");
    exit(1);
}

$xml = simplexml_load_file($cloverFile);
if ($xml === false) {
    fwrite(STDERR, "Invalid XML\n");
    exit(1);
}

/*
 * Clover structure:
 *
 * <file name="src/Foo.php">
 *   <line num="123" type="stmt" count="0"/>
 * </file>
 */

$containerPrefix = '/var/app/';
$filesWithUncoveredLines = [];

foreach ($xml->project->file as $file) {
    $fileName = (string) $file['name'];

    // Strip container prefix if present
    if (str_starts_with($fileName, $containerPrefix)) {
        $fileName = substr($fileName, strlen($containerPrefix));
    }

    foreach ($file->line as $line) {
        $type  = (string) $line['type'];
        $count = (int)    $line['count'];
        $num   = (int)    $line['num'];

        if ($type !== 'stmt') {
            continue;
        }

        if ($count === 0) {
            if ($mode === 'improve_test_coverage') {
                $filesWithUncoveredLines[$fileName] = true;
                continue;
            }

            echo $fileName . ':' . $num . PHP_EOL;
        }
    }
}

if ($mode === 'improve_test_coverage') {
    foreach (array_keys($filesWithUncoveredLines) as $fileName) {
        echo '/improve_test_coverage ' . $fileName . PHP_EOL;
    }
}
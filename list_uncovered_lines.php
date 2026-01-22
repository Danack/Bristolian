<?php


/**
 * Usage:
 *   php list_uncovered_lines.php clover.xml
 *
 * Output:
 *   path/to/file.php:LINE
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php list_uncovered_lines.php clover.xml\n");
    exit(1);
}

$cloverFile = $argv[1];

if (!file_exists($cloverFile)) {
    fwrite(STDERR, "File not found: $cloverFile\n");
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
            echo $fileName . ':' . $num . PHP_EOL;
        }
    }
}
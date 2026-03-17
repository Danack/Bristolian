<?php

declare(strict_types=1);

/**
 * Lists uncovered statement lines from Istanbul/nyc-generated Clover XML
 * (frontend JS/TS coverage from Behat).
 *
 * Usage:
 *   php list_uncovered_frontend_lines.php tmp/behat-js-coverage-report/clover.xml
 *
 * Output:
 *   path/to/file.tsx:LINE
 *
 * The Istanbul Clover format nests files under project -> package -> file
 * and uses a "path" attribute for the full file path.
 */

if ($argc < 2) {
    fwrite(STDERR, "Usage: php list_uncovered_frontend_lines.php clover.xml\n");
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
 * Istanbul/nyc Clover structure:
 *
 * <coverage>
 *   <project>
 *     <package name="tsx">
 *       <file name="AnnotationPanel.tsx" path="/var/app/app/public/tsx/AnnotationPanel.tsx">
 *         <line num="40" count="0" type="stmt"/>
 *       </file>
 *     </package>
 *   </project>
 * </coverage>
 */

$containerPrefix = '/var/app/';

foreach ($xml->project->package ?? [] as $package) {
    foreach ($package->file ?? [] as $file) {
        $path = (string) ($file['path'] ?? $file['name'] ?? '');
        if ($path === '') {
            continue;
        }

        if (str_starts_with($path, $containerPrefix)) {
            $path = substr($path, strlen($containerPrefix));
        }

        foreach ($file->line ?? [] as $line) {
            $type  = (string) $line['type'];
            $count = (int) $line['count'];
            $num   = (int) $line['num'];

            if ($type !== 'stmt') {
                continue;
            }

            if ($count === 0) {
                echo $path . ':' . $num . PHP_EOL;
            }
        }
    }
}

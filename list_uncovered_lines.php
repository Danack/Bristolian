<?php


/**
 * Usage:
 *   php list_uncovered_lines.php clover.xml
 *
 * Output:
 *   path/to/file.php:LINE
 *   or
 *   path/to/file.php:LINE_START-LINE_END
 */

//if ($argc < 2) {
//    fwrite(STDERR, "Usage: php list_uncovered_lines.php clover.xml\n");
//
//    var_dump($argv);
//    exit(1);
//}

$cloverFile = "clover.xml";

if ($argc > 1) {
    $cloverFile = $argv[1];
}


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
 * Clover structure (PHPUnit 10+ may nest namespaced files under <package>):
 *
 * <project>
 *   <package name="Bristolian\Foo">
 *     <file name="src/Bristolian/Foo.php">
 *       <line num="123" type="stmt" count="0"/>
 *     </file>
 *   </package>
 *   <file name="src/functions.php">...</file>
 * </project>
 */

$containerPrefix = '/var/app/';
$filesWithUncoveredLines = [];

foreach ($xml->xpath('//file') as $file) {
    $fileName = (string) $file['name'];

    // Strip container prefix if present
    if (str_starts_with($fileName, $containerPrefix)) {
        $fileName = substr($fileName, strlen($containerPrefix));
    }

    $uncoveredLines = [];

    foreach ($file->line as $line) {
        $type  = (string) $line['type'];
        $count = (int)    $line['count'];
        $num   = (int)    $line['num'];

        if ($type !== 'stmt') {
            continue;
        }

        if ($count === 0) {
            $uncoveredLines[] = $num;
        }
    }

    // Group contiguous lines into ranges
    $start = null;
    $previous = null;

    foreach ($uncoveredLines as $num) {
        if ($start === null) {
            $start = $num;
        } elseif ($num !== $previous + 1) {
            echo $fileName . ':' . $start;

            if ($start !== $previous) {
                echo '-' . $previous;
            }

            echo PHP_EOL;

            $start = $num;
        }

        $previous = $num;
    }

    // Output the final range
    if ($start !== null) {
        echo $fileName . ':' . $start;

        if ($start !== $previous) {
            echo '-' . $previous;
        }

        echo PHP_EOL;
    }
}

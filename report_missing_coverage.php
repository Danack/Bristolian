<?php

declare(strict_types=1);

/**
 * Report missing PHP unit-test coverage from a Clover XML file.
 *
 * Usage (inside php_fpm container, after tests have produced clover.xml):
 *   php report_missing_coverage.php
 *   php report_missing_coverage.php clover.xml
 *   php report_missing_coverage.php --filter=ExplorerData
 *   php report_missing_coverage.php --limit=15 --lines
 *   php report_missing_coverage.php --json
 *
 * From the host:
 *   docker exec bristolian-php_fpm-1 bash -c "php report_missing_coverage.php"
 *   docker exec bristolian-php_fpm-1 bash -c "php report_missing_coverage.php --json"
 *
 * Options:
 *   --filter=TEXT   Only include files whose path contains TEXT
 *   --limit=N       Show at most N files with gaps (default: all with gaps)
 *   --lines         Text mode: list uncovered line numbers per file
 *   --json          Emit machine-readable JSON to stdout instead of text
 *   --help          Show this help
 *
 * On every successful run, also writes cache files beside this script:
 *   report_missing_coverage.php.output.json
 *   report_missing_coverage.php.output.llm
 *
 * generated_at is ISO-8601 UTC (e.g. 2026-07-27T11:43:39Z) so host tools
 * comparing it to file mtimes do not mis-parse container UTC as local time.
 * JSON also includes generated_at_unix (seconds since epoch) for unambiguous compares.
 *
 * Exit codes:
 *   0  Report produced successfully (gaps, if any, are in the output — not an error)
 *   2  Usage / file / parse error
 *
 * Consumers (CodeView, agents) should read the cache/JSON for coverage gaps, not the exit code.
 */

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
    echo <<<'HELP'
Report missing PHP coverage from clover.xml

Usage (inside php_fpm, after tests have produced clover.xml):
  php report_missing_coverage.php
  php report_missing_coverage.php clover.xml
  php report_missing_coverage.php --filter=ExplorerData
  php report_missing_coverage.php --limit=15 --lines
  php report_missing_coverage.php --json

From the host:
  docker exec bristolian-php_fpm-1 bash -c "php report_missing_coverage.php"
  docker exec bristolian-php_fpm-1 bash -c "php report_missing_coverage.php --json"

Options:
  --filter=TEXT   Only include files whose path contains TEXT
  --limit=N       Show at most N files with gaps (default: all with gaps)
  --lines         Text mode: list uncovered line numbers per file
  --json          Emit machine-readable JSON to stdout instead of text
  --help          Show this help

Cache files (written on every successful run):
  report_missing_coverage.php.output.json
  report_missing_coverage.php.output.llm

Exit codes:
  0  Report produced successfully (gaps are in the output, not an error)
  2  Usage / file / parse error

HELP;
    exit(0);
}

/** ISO-8601 UTC for CodeView staleness checks (must not be naive local wall-clock). */
const GENERATED_AT_FORMAT = 'Y-m-d\TH:i:s\Z';

$cloverFile = 'clover.xml';
$filter = null;
$limit = null;
$showLines = false;
$jsonOutput = false;

foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--filter=')) {
        $filter = substr($argument, strlen('--filter='));
        continue;
    }
    if (str_starts_with($argument, '--limit=')) {
        $limitValue = substr($argument, strlen('--limit='));
        if (ctype_digit($limitValue) === false || (int) $limitValue < 1) {
            fwrite(STDERR, "Invalid --limit value: {$limitValue}\n");
            exit(2);
        }
        $limit = (int) $limitValue;
        continue;
    }
    if ($argument === '--lines') {
        $showLines = true;
        continue;
    }
    if ($argument === '--json') {
        $jsonOutput = true;
        continue;
    }
    if (str_starts_with($argument, '-')) {
        fwrite(STDERR, "Unknown option: {$argument}\n");
        exit(2);
    }
    $cloverFile = $argument;
}

if (is_file($cloverFile) === false) {
    fwrite(STDERR, "Clover file not found: {$cloverFile}\n");
    fwrite(STDERR, "Generate it with: sh runUnitTests.sh --no-progress\n");
    exit(2);
}

$xml = simplexml_load_file($cloverFile);
if ($xml === false) {
    fwrite(STDERR, "Invalid Clover XML: {$cloverFile}\n");
    exit(2);
}

$containerPrefix = '/var/app/';

/**
 * @return array{
 *   path: string,
 *   statements: int,
 *   covered: int,
 *   uncovered: int,
 *   uncovered_lines: list<int>
 * }
 */
function analyseCloverFile(SimpleXMLElement $file, string $containerPrefix): array
{
    $path = (string) $file['name'];
    if (str_starts_with($path, $containerPrefix)) {
        $path = substr($path, strlen($containerPrefix));
    }

    $statements = 0;
    $covered = 0;
    $uncoveredLines = [];

    foreach ($file->line as $line) {
        if ((string) $line['type'] !== 'stmt') {
            continue;
        }

        $statements++;
        $count = (int) $line['count'];
        if ($count > 0) {
            $covered++;
            continue;
        }

        $uncoveredLines[] = (int) $line['num'];
    }

    return [
        'path' => $path,
        'statements' => $statements,
        'covered' => $covered,
        'uncovered' => $statements - $covered,
        'uncovered_lines' => $uncoveredLines,
    ];
}

/**
 * @param list<array{path: string, statements: int, covered: int, uncovered: int, uncovered_lines: list<int>}> $files
 * @return list<array{path: string, statements: int, covered: int, uncovered: int, files: int}>
 */
function aggregateByDirectory(array $files): array
{
    $directories = [];

    foreach ($files as $file) {
        if ($file['uncovered'] === 0) {
            continue;
        }

        $directory = dirname($file['path']);
        if (array_key_exists($directory, $directories) === false) {
            $directories[$directory] = [
                'path' => $directory,
                'statements' => 0,
                'covered' => 0,
                'uncovered' => 0,
                'files' => 0,
            ];
        }

        $directories[$directory]['statements'] += $file['statements'];
        $directories[$directory]['covered'] += $file['covered'];
        $directories[$directory]['uncovered'] += $file['uncovered'];
        $directories[$directory]['files']++;
    }

    $directoryList = array_values($directories);
    usort(
        $directoryList,
        static function (array $left, array $right): int {
            $uncoveredComparison = $right['uncovered'] <=> $left['uncovered'];
            if ($uncoveredComparison !== 0) {
                return $uncoveredComparison;
            }

            return strcmp($left['path'], $right['path']);
        }
    );

    return $directoryList;
}

function coveragePercent(int $covered, int $total): ?float
{
    if ($total === 0) {
        return null;
    }

    return round(($covered / $total) * 100, 2);
}

function formatPercent(int $covered, int $total): string
{
    $percent = coveragePercent($covered, $total);
    if ($percent === null) {
        return 'n/a';
    }

    return number_format($percent, 2) . '%';
}

/**
 * @return array{covered: int, total: int, uncovered: int, percent: float|null}
 */
function metricBlock(int $covered, int $total): array
{
    return [
        'covered' => $covered,
        'total' => $total,
        'uncovered' => $total - $covered,
        'percent' => coveragePercent($covered, $total),
    ];
}

function printSectionTo(string $title, string &$buffer): void
{
    $buffer .= "\n" . $title . "\n";
    $buffer .= str_repeat('-', strlen($title)) . "\n";
}

/**
 * @param list<array{path: string, statements: int, covered: int, uncovered: int, uncovered_lines: list<int>}> $filesShown
 * @param list<array{path: string, statements: int, covered: int, uncovered: int, files: int}> $directoriesShown
 * @param list<array{path: string, statements: int, covered: int, uncovered: int, uncovered_lines: list<int>}> $filesWithGaps
 * @param array<string, mixed> $summary
 */
function buildTextReport(
    string $cloverFile,
    ?string $filter,
    string $generatedAt,
    int $totalCovered,
    int $totalStatements,
    int $totalUncovered,
    int $metricCoveredMethods,
    int $metricMethods,
    int $metricCoveredClasses,
    int $metricClasses,
    array $summary,
    array $filesWithGaps,
    array $allFiles,
    array $directoriesShown,
    array $filesShown,
    ?int $limit,
    bool $showLines,
): string {
    $buffer = '';
    $buffer .= "generated_at: {$generatedAt}\n";
    $buffer .= "Missing PHP coverage report\n";
    $buffer .= "===================\n";
    $buffer .= "Source: {$cloverFile}\n";
    if ($filter !== null) {
        $buffer .= "Filter: {$filter}\n";
    }
    $buffer .= "Generated: {$generatedAt}\n";

    printSectionTo('Summary', $buffer);
    $buffer .= sprintf(
        "  Statements : %d / %d  (%s)   uncovered: %d\n",
        $totalCovered,
        $totalStatements,
        formatPercent($totalCovered, $totalStatements),
        $totalUncovered
    );

    if ($summary['methods'] !== null) {
        $buffer .= sprintf(
            "  Methods    : %d / %d  (%s)\n",
            $metricCoveredMethods,
            $metricMethods,
            formatPercent($metricCoveredMethods, $metricMethods)
        );
    }

    if ($summary['classes'] !== null) {
        $buffer .= sprintf(
            "  Classes    : %d / %d  (%s)\n",
            $metricCoveredClasses,
            $metricClasses,
            formatPercent($metricCoveredClasses, $metricClasses)
        );
    }

    $buffer .= sprintf(
        "  Files      : %d with gaps / %d analysed\n",
        count($filesWithGaps),
        count($allFiles)
    );

    if ($filesWithGaps === []) {
        printSectionTo('Result', $buffer);
        $buffer .= "  All analysed statements are covered.\n";
        return $buffer;
    }

    printSectionTo('Directories needing more tests', $buffer);
    $buffer .= sprintf("  %-8s %-6s  %s\n", 'Uncov', 'Files', 'Directory');
    foreach ($directoriesShown as $directoryStats) {
        $buffer .= sprintf(
            "  %8d %6d  %s\n",
            $directoryStats['uncovered'],
            $directoryStats['files'],
            $directoryStats['path']
        );
    }

    printSectionTo('Files needing more tests', $buffer);
    if ($limit !== null && count($filesWithGaps) > $limit) {
        $buffer .= "  Showing top {$limit} of " . count($filesWithGaps) . " files with gaps.\n";
    }
    $buffer .= sprintf("  %-8s %-8s %-8s %-7s  %s\n", 'Uncov', 'Covered', 'Total', 'Cover%', 'File');
    foreach ($filesShown as $file) {
        $buffer .= sprintf(
            "  %8d %8d %8d %7s  %s\n",
            $file['uncovered'],
            $file['covered'],
            $file['statements'],
            formatPercent($file['covered'], $file['statements']),
            $file['path']
        );
    }

    if ($showLines === true) {
        printSectionTo('Uncovered lines', $buffer);
        foreach ($filesShown as $file) {
            $buffer .= "  {$file['path']}\n";
            $buffer .= '    ' . implode(', ', $file['uncovered_lines']) . "\n";
        }
    }

    $buffer .= "\n";

    return $buffer;
}

/**
 * @param list<array{path: string, statements: int, covered: int, uncovered: int, uncovered_lines: list<int>}> $filesWithGaps
 * @param list<array{path: string, statements: int, covered: int, uncovered: int, files: int}> $directoriesShown
 * @param list<array{path: string, statements: int, covered: int, uncovered: int, uncovered_lines: list<int>}> $filesShown
 * @param array<string, mixed> $summary
 * @return array<string, mixed>
 */
function buildJsonPayload(
    string $cloverFile,
    string $generatedAt,
    int $generatedAtUnix,
    ?string $filter,
    ?int $limit,
    array $summary,
    array $directoriesShown,
    array $filesShown,
    array $filesWithGaps,
): array {
    $jsonFiles = [];
    foreach ($filesShown as $file) {
        $jsonFiles[] = [
            'path' => $file['path'],
            'statements' => $file['statements'],
            'covered' => $file['covered'],
            'uncovered' => $file['uncovered'],
            'percent' => coveragePercent($file['covered'], $file['statements']),
            'uncovered_lines' => $file['uncovered_lines'],
        ];
    }

    $allGapFiles = [];
    foreach ($filesWithGaps as $file) {
        $allGapFiles[] = [
            'path' => $file['path'],
            'statements' => $file['statements'],
            'covered' => $file['covered'],
            'uncovered' => $file['uncovered'],
            'percent' => coveragePercent($file['covered'], $file['statements']),
            'uncovered_lines' => $file['uncovered_lines'],
        ];
    }

    return [
        'source' => $cloverFile,
        'generated_at' => $generatedAt,
        'generated_at_unix' => $generatedAtUnix,
        'filter' => $filter,
        'limit' => $limit,
        'summary' => $summary,
        'directories' => $directoriesShown,
        'files' => $jsonFiles,
        'files_with_gaps' => $allGapFiles,
    ];
}

function writeCacheOutputs(string $scriptPath, string $jsonContent, string $llmContent): void
{
    $jsonPath = $scriptPath . '.output.json';
    $llmPath = $scriptPath . '.output.llm';

    if (file_put_contents($jsonPath, $jsonContent) === false) {
        fwrite(STDERR, "Failed to write cache file: {$jsonPath}\n");
        exit(2);
    }

    if (file_put_contents($llmPath, $llmContent) === false) {
        fwrite(STDERR, "Failed to write cache file: {$llmPath}\n");
        exit(2);
    }

    // Restamp after write: sibling .output.* mtimes are often a few hundred ms
    // newer than the generated_at captured before writing. If CodeView treats those
    // cache files as staleness inputs, the report looks "out of date" immediately.
    clearstatcache();
    $jsonMtime = is_file($jsonPath) ? (int) filemtime($jsonPath) : time();
    $llmMtime = is_file($llmPath) ? (int) filemtime($llmPath) : $jsonMtime;
    $restampedUnix = max(time(), $jsonMtime, $llmMtime);
    $restampedAt = gmdate(GENERATED_AT_FORMAT, $restampedUnix);

    $decoded = json_decode($jsonContent, true);
    if (is_array($decoded) === false) {
        fwrite(STDERR, "Failed to decode cache JSON for restamp: {$jsonPath}\n");
        exit(2);
    }

    $decoded['generated_at'] = $restampedAt;
    $decoded['generated_at_unix'] = $restampedUnix;
    $restampedJson = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    if ($restampedJson === false || file_put_contents($jsonPath, $restampedJson) === false) {
        fwrite(STDERR, "Failed to rewrite cache file with restamped generated_at: {$jsonPath}\n");
        exit(2);
    }

    $restampedLlm = preg_replace(
        '/^generated_at:.*\n/',
        'generated_at: ' . $restampedAt . "\n",
        $llmContent,
        1
    );
    if (is_string($restampedLlm) === false || file_put_contents($llmPath, $restampedLlm) === false) {
        fwrite(STDERR, "Failed to rewrite cache llm with restamped generated_at: {$llmPath}\n");
        exit(2);
    }

    // Touch both cache files to the restamp second so mtime is not > generated_at.
    touch($jsonPath, $restampedUnix);
    touch($llmPath, $restampedUnix);
}

$allFiles = [];
$totalStatements = 0;
$totalCovered = 0;

foreach ($xml->xpath('//file') as $fileNode) {
    $analysis = analyseCloverFile($fileNode, $containerPrefix);

    if ($filter !== null && str_contains($analysis['path'], $filter) === false) {
        continue;
    }

    if ($analysis['statements'] === 0) {
        continue;
    }

    $allFiles[] = $analysis;
    $totalStatements += $analysis['statements'];
    $totalCovered += $analysis['covered'];
}

usort(
    $allFiles,
    static function (array $left, array $right): int {
        $uncoveredComparison = $right['uncovered'] <=> $left['uncovered'];
        if ($uncoveredComparison !== 0) {
            return $uncoveredComparison;
        }

        return strcmp($left['path'], $right['path']);
    }
);

$totalUncovered = $totalStatements - $totalCovered;

$filesWithGaps = array_values(array_filter(
    $allFiles,
    static fn (array $file): bool => $file['uncovered'] > 0
));

$filesShown = $filesWithGaps;
if ($limit !== null) {
    $filesShown = array_slice($filesWithGaps, 0, $limit);
}

$projectMetrics = $xml->project->metrics ?? null;
$metricMethods = $projectMetrics !== null ? (int) $projectMetrics['methods'] : 0;
$metricCoveredMethods = $projectMetrics !== null ? (int) $projectMetrics['coveredmethods'] : 0;

$metricClasses = 0;
$metricCoveredClasses = 0;
if ($projectMetrics !== null && isset($projectMetrics['coveredclasses'])) {
    $metricClasses = (int) $projectMetrics['classes'];
    $metricCoveredClasses = (int) $projectMetrics['coveredclasses'];
} else {
    foreach ($xml->xpath('//class') as $classNode) {
        $classStatements = (int) $classNode->metrics['statements'];
        if ($classStatements === 0) {
            continue;
        }
        $metricClasses++;
        if ((int) $classNode->metrics['coveredstatements'] === $classStatements) {
            $metricCoveredClasses++;
        }
    }
}

$directories = aggregateByDirectory($filesWithGaps);
$directoriesShown = $directories;
if ($limit !== null) {
    $directoriesShown = array_slice($directories, 0, min(15, $limit));
} else {
    $directoriesShown = array_slice($directories, 0, 15);
}

$generatedAtUnix = time();
$generatedAt = gmdate(GENERATED_AT_FORMAT, $generatedAtUnix);

$summary = [
    'statements' => metricBlock($totalCovered, $totalStatements),
    'methods' => ($filter === null && $projectMetrics !== null)
        ? metricBlock($metricCoveredMethods, $metricMethods)
        : null,
    'classes' => ($filter === null)
        ? metricBlock($metricCoveredClasses, $metricClasses)
        : null,
    'files_analysed' => count($allFiles),
    'files_with_gaps' => count($filesWithGaps),
];

$payload = buildJsonPayload(
    $cloverFile,
    $generatedAt,
    $generatedAtUnix,
    $filter,
    $limit,
    $summary,
    $directoriesShown,
    $filesShown,
    $filesWithGaps,
);

$textReport = buildTextReport(
    $cloverFile,
    $filter,
    $generatedAt,
    $totalCovered,
    $totalStatements,
    $totalUncovered,
    $metricCoveredMethods,
    $metricMethods,
    $metricCoveredClasses,
    $metricClasses,
    $summary,
    $filesWithGaps,
    $allFiles,
    $directoriesShown,
    $filesShown,
    $limit,
    $showLines,
);

$jsonContent = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
writeCacheOutputs(__FILE__, $jsonContent, $textReport);

if ($jsonOutput === true) {
    echo $jsonContent;
    exit(0);
}

echo $textReport;
exit(0);

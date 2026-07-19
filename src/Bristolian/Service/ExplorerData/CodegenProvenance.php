<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

/**
 * Machine-readable provenance for auto-generation processes (CodeView / agents).
 *
 * One CodeView entry per generation process (not per emitted file). Headers in
 * generated files use CODEVIEW_GENERATED_BEGIN / END markers around the same JSON.
 */
class CodegenProvenance
{
    public const BEGIN_MARKER = 'CODEVIEW_GENERATED_BEGIN';

    public const END_MARKER = 'CODEVIEW_GENERATED_END';

    /**
     * @return array{
     *     generator: string,
     *     generator_callable: string,
     *     output_file: string,
     *     description: string,
     *     detail?: string,
     *     detail_source?: array{file: string, line-start: int, line-end: int}
     * }
     */
    public static function buildPayload(
        string $generator,
        string $generatorCallable,
        string $outputFile,
        ?string $detail = null,
        ?array $detailSource = null
    ): array {
        $normalizedCallable = ControllerCallableCodeLocator::normalizeFqcn($generatorCallable);

        $payload = [
            'generator' => $generator,
            'generator_callable' => $normalizedCallable,
            'output_file' => $outputFile,
            'description' => self::descriptionForCallable($normalizedCallable),
        ];

        if ($detail !== null && $detail !== '') {
            $payload['detail'] = $detail;
        }

        if ($detailSource !== null) {
            $payload['detail_source'] = [
                'file' => $detailSource['file'],
                'line-start' => $detailSource['line-start'],
                'line-end' => $detailSource['line-end'],
            ];
        }

        return $payload;
    }

    /**
     * Prose from the callable's docblock (tags stripped). Empty string if none.
     *
     * Docblock formatting convention for codeview descriptions:
     *
     * - Flush-left lines (immediately after "* ") are soft-wrapped: consecutive
     *   flush lines are joined with a single space so mid-sentence line breaks in
     *   the PHP source do not appear in codeview-data.json.
     * - Indented lines (one or more spaces after "* ") are structural: each is kept
     *   on its own line. Use this for lists, tuples, and other deliberate layout.
     * - A blank docblock line starts a new paragraph (paragraphs separated by \n\n).
     * - @tags end the prose section.
     */
    public static function descriptionForCallable(string $generatorCallable): string
    {
        [$className, $methodName] = self::parseCallable($generatorCallable);
        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        $docComment = $reflectionMethod->getDocComment();

        if ($docComment === false) {
            return '';
        }

        /** @var list<array{type: 'soft'|'hard', lines: list<string>}> $blocks */
        $blocks = [];
        $currentType = null;
        /** @var list<string> $currentLines */
        $currentLines = [];

        $flush = static function () use (&$blocks, &$currentType, &$currentLines): void {
            if ($currentType === null || $currentLines === []) {
                $currentType = null;
                $currentLines = [];
                return;
            }

            $blocks[] = [
                'type' => $currentType,
                'lines' => $currentLines,
            ];
            $currentType = null;
            $currentLines = [];
        };

        foreach (explode("\n", $docComment) as $line) {
            $line = preg_replace('#\s*\*/\s*$#', '', $line) ?? $line;
            $line = preg_replace('#^\s*/\*\*?\s*#', '', $line) ?? $line;

            if (preg_match('#^\s*\* ?(.*)$#', $line, $matches) === 1) {
                $content = rtrim($matches[1]);
            }
            else {
                $content = rtrim($line);
            }

            $contentTrimmedLeft = ltrim($content);

            if (str_starts_with($contentTrimmedLeft, '@')) {
                break;
            }

            if ($content === '') {
                $flush();
                continue;
            }

            $isStructural = preg_match('#^\s#', $content) === 1;
            $blockType = $isStructural ? 'hard' : 'soft';
            $lineText = $isStructural ? $content : $contentTrimmedLeft;

            if ($currentType !== null && $currentType !== $blockType) {
                $flush();
            }

            $currentType = $blockType;
            $currentLines[] = $lineText;
        }

        $flush();

        $paragraphs = [];
        foreach ($blocks as $block) {
            if ($block['type'] === 'soft') {
                $paragraphs[] = implode(' ', $block['lines']);
            }
            else {
                $paragraphs[] = implode("\n", $block['lines']);
            }
        }

        return implode("\n\n", $paragraphs);
    }

    /**
     * Raw PHP source of `$variableName = ...;` inside the callable method body.
     */
    public static function extractAssignmentSource(
        string $generatorCallable,
        string $variableName
    ): string {
        return self::extractAssignment($generatorCallable, $variableName)['text'];
    }

    /**
     * @return array{text: string, file: string, line-start: int, line-end: int}
     */
    public static function extractAssignment(
        string $generatorCallable,
        string $variableName
    ): array {
        [$className, $methodName] = self::parseCallable($generatorCallable);
        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        $fileName = $reflectionMethod->getFileName();

        if ($fileName === false) {
            throw new \RuntimeException('Could not resolve file for callable: ' . $generatorCallable);
        }

        $fileContents = file_get_contents($fileName);
        if ($fileContents === false) {
            throw new \RuntimeException('Could not read file for callable: ' . $generatorCallable);
        }

        $lines = explode("\n", $fileContents);
        $methodStartLine = $reflectionMethod->getStartLine();
        $methodEndLine = $reflectionMethod->getEndLine();

        if ($methodStartLine === false || $methodEndLine === false) {
            throw new \RuntimeException('Could not resolve line range for callable: ' . $generatorCallable);
        }

        // Reflection lines are 1-based inclusive.
        $methodSource = implode(
            "\n",
            array_slice($lines, $methodStartLine - 1, $methodEndLine - $methodStartLine + 1)
        );

        $needle = '$' . $variableName;
        $assignmentStart = strpos($methodSource, $needle . ' =');
        if ($assignmentStart === false) {
            $assignmentStart = strpos($methodSource, $needle . '=');
        }

        if ($assignmentStart === false) {
            throw new \RuntimeException(
                "Could not find assignment for \${$variableName} in {$generatorCallable}"
            );
        }

        $fromAssignment = substr($methodSource, $assignmentStart);
        $length = strlen($fromAssignment);
        $bracketDepth = 0;
        $seenOpeningBracket = false;
        $assignmentEndOffset = null;

        for ($index = 0; $index < $length; $index++) {
            $character = $fromAssignment[$index];

            if ($character === '[') {
                $bracketDepth++;
                $seenOpeningBracket = true;
            }
            elseif ($character === ']') {
                $bracketDepth--;
            }
            elseif ($character === ';' && $seenOpeningBracket === true && $bracketDepth === 0) {
                $assignmentEndOffset = $index;
                break;
            }
        }

        if ($assignmentEndOffset === null) {
            throw new \RuntimeException(
                "Could not extract assignment for \${$variableName} in {$generatorCallable}"
            );
        }

        $text = rtrim(substr($fromAssignment, 0, $assignmentEndOffset + 1));

        // Convert offsets within the method body into 1-based file line numbers.
        $prefixBeforeAssignment = substr($methodSource, 0, $assignmentStart);
        $lineStart = $methodStartLine + substr_count($prefixBeforeAssignment, "\n");
        $lineEnd = $lineStart + substr_count($text, "\n");

        $projectRoot = dirname(__DIR__, 4);

        return [
            'text' => $text,
            'file' => self::projectRelativePath($fileName, $projectRoot),
            'line-start' => $lineStart,
            'line-end' => $lineEnd,
        ];
    }

    /**
     * Extract one or more assignments and merge into a single detail + source span.
     *
     * @param list<string> $variableNames
     * @return array{
     *     detail: string,
     *     detail_source: array{file: string, line-start: int, line-end: int}
     * }
     */
    public static function extractAssignments(
        string $generatorCallable,
        array $variableNames
    ): array {
        if ($variableNames === []) {
            throw new \RuntimeException('extractAssignments requires at least one variable name.');
        }

        $chunks = [];
        $file = null;
        $lineStart = null;
        $lineEnd = null;

        foreach ($variableNames as $variableName) {
            $assignment = self::extractAssignment($generatorCallable, $variableName);
            $chunks[] = $assignment['text'];

            if ($file === null) {
                $file = $assignment['file'];
            }
            elseif ($file !== $assignment['file']) {
                throw new \RuntimeException(
                    'extractAssignments found assignments in different files for ' . $generatorCallable
                );
            }

            if ($lineStart === null || $assignment['line-start'] < $lineStart) {
                $lineStart = $assignment['line-start'];
            }
            if ($lineEnd === null || $assignment['line-end'] > $lineEnd) {
                $lineEnd = $assignment['line-end'];
            }
        }

        return [
            'detail' => implode("\n\n", $chunks),
            'detail_source' => [
                'file' => $file,
                'line-start' => $lineStart,
                'line-end' => $lineEnd,
            ],
        ];
    }

    public static function projectRelativePath(string $absolutePath, string $projectRoot): string
    {
        $projectRootWithSeparator = rtrim($projectRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (str_starts_with($absolutePath, $projectRootWithSeparator)) {
            return str_replace('\\', '/', substr($absolutePath, strlen($projectRootWithSeparator)));
        }

        return str_replace('\\', '/', $absolutePath);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function formatLineComment(array $payload): string
    {
        $json = json_encode(
            $payload,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        if ($json === false) {
            throw new \RuntimeException('Failed to encode codegen provenance payload as JSON.');
        }

        $out = '// ' . self::BEGIN_MARKER . "\n";
        foreach (explode("\n", $json) as $line) {
            $out .= '// ' . $line . "\n";
        }
        $out .= '// ' . self::END_MARKER . "\n";

        return $out;
    }

    /**
     * Human-readable TypeScript preamble (kept for operators); provenance block is separate.
     */
    public static function typescriptHumanPreamble(string $cliCommand, string $generatorCallable): string
    {
        $normalizedCallable = ControllerCallableCodeLocator::normalizeFqcn($generatorCallable);

        return "// This is an auto-generated file\n"
            . "// DO NOT EDIT\n\n"
            . "// You'll need to bounce the docker boxes to regenerate.\n"
            . "//\n"
            . "// or run 'php cli.php {$cliCommand}'\n"
            . "// Code for generating this file is in \\{$normalizedCallable}\n\n";
    }

    /**
     * @return array{
     *     generator: string,
     *     generator_callable: string,
     *     output_file: string,
     *     description: string,
     *     detail?: string,
     *     detail_source?: array{file: string, line-start: int, line-end: int}
     * }|null
     */
    public static function parseFromFileContents(string $contents): ?array
    {
        $beginPosition = strpos($contents, self::BEGIN_MARKER);
        $endPosition = strpos($contents, self::END_MARKER);

        if ($beginPosition === false || $endPosition === false || $endPosition <= $beginPosition) {
            return null;
        }

        $between = substr(
            $contents,
            $beginPosition + strlen(self::BEGIN_MARKER),
            $endPosition - $beginPosition - strlen(self::BEGIN_MARKER)
        );

        $jsonLines = [];
        foreach (explode("\n", $between) as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            if (str_starts_with($trimmed, '//')) {
                $trimmed = ltrim(substr($trimmed, 2));
            }
            if (str_starts_with($trimmed, '*')) {
                $trimmed = ltrim(substr($trimmed, 1));
            }
            if ($trimmed === '') {
                continue;
            }
            $jsonLines[] = $trimmed;
        }

        $json = implode("\n", $jsonLines);
        $decoded = json_decode($json, true);

        if (is_array($decoded) === false) {
            return null;
        }

        if (
            array_key_exists('generator', $decoded) === false
            || array_key_exists('generator_callable', $decoded) === false
            || array_key_exists('output_file', $decoded) === false
            || array_key_exists('description', $decoded) === false
        ) {
            return null;
        }

        if (is_string($decoded['generator']) === false
            || is_string($decoded['generator_callable']) === false
            || is_string($decoded['output_file']) === false
            || is_string($decoded['description']) === false
        ) {
            return null;
        }

        $payload = [
            'generator' => $decoded['generator'],
            'generator_callable' => $decoded['generator_callable'],
            'output_file' => $decoded['output_file'],
            'description' => $decoded['description'],
        ];

        if (
            array_key_exists('detail', $decoded) === true
            && is_string($decoded['detail']) === true
            && $decoded['detail'] !== ''
        ) {
            $payload['detail'] = $decoded['detail'];
        }

        if (
            array_key_exists('detail_source', $decoded) === true
            && is_array($decoded['detail_source']) === true
            && array_key_exists('file', $decoded['detail_source']) === true
            && array_key_exists('line-start', $decoded['detail_source']) === true
            && array_key_exists('line-end', $decoded['detail_source']) === true
            && is_string($decoded['detail_source']['file']) === true
            && is_int($decoded['detail_source']['line-start']) === true
            && is_int($decoded['detail_source']['line-end']) === true
        ) {
            $payload['detail_source'] = [
                'file' => $decoded['detail_source']['file'],
                'line-start' => $decoded['detail_source']['line-start'],
                'line-end' => $decoded['detail_source']['line-end'],
            ];
        }

        return $payload;
    }

    /**
     * @return array{0: class-string, 1: string}
     */
    private static function parseCallable(string $generatorCallable): array
    {
        $normalizedCallable = ControllerCallableCodeLocator::normalizeFqcn($generatorCallable);
        $parts = explode('::', $normalizedCallable, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new \RuntimeException('Invalid generator callable: ' . $generatorCallable);
        }

        /** @var class-string $className */
        $className = $parts[0];

        return [$className, $parts[1]];
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\CodegenProvenance;
use Bristolian\Service\ExplorerData\GeneratedArtifactsEntryTypeFinder;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @coversNothing
 */
class CodegenProvenanceTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::formatLineComment
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseFromFileContents
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::buildPayload
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::descriptionForCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::typescriptHumanPreamble
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::flushDescriptionBlock
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::normalizeFqcn
     */
    public function test_format_and_parse_round_trip(): void
    {
        $payload = CodegenProvenance::buildPayload(
            'generate:javascript_constants',
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            'app/public/tsx/generated/constants.tsx',
            '$constantDefinitions = [];'
        );

        $fileContents = CodegenProvenance::typescriptHumanPreamble(
            'generate:javascript_constants',
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants'
        );
        $fileContents .= CodegenProvenance::formatLineComment($payload);
        $fileContents .= "\nexport const MEME_FILE_UPLOAD_FORM_NAME: string = \"meme_file\";\n";

        $this->assertStringContainsString("bounce the docker boxes", $fileContents);
        $this->assertStringContainsString("php cli.php generate:javascript_constants", $fileContents);
        $this->assertNotSame('', $payload['description']);
        $this->assertStringContainsString('TypeScript', $payload['description']);
        $this->assertStringContainsString(
            'need to be shared between the front',
            $payload['description']
        );
        $this->assertStringNotContainsString(
            "shared\nbetween",
            $payload['description']
        );

        $parsed = CodegenProvenance::parseFromFileContents($fileContents);
        $this->assertIsArray($parsed);
        $this->assertSame($payload['generator'], $parsed['generator']);
        $this->assertSame($payload['generator_callable'], $parsed['generator_callable']);
        $this->assertSame($payload['output_file'], $parsed['output_file']);
        $this->assertSame($payload['description'], $parsed['description']);
        $this->assertSame($payload['detail'], $parsed['detail']);
        $this->assertArrayNotHasKey('sources', $parsed);
        $this->assertArrayNotHasKey('mappings', $parsed);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::descriptionForCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::flushDescriptionBlock
     */
    public function test_description_preserves_indented_structure_as_newlines(): void
    {
        $description = CodegenProvenance::descriptionForCallable(
            'Bristolian\\CliController\\GenerateFiles::generateTypeScriptApiRoutes'
        );

        $this->assertStringContainsString(
            'from api/src/api_routes.php into app/public/tsx/generated/api_routes.tsx.',
            $description
        );
        $this->assertStringContainsString("[0] path string", $description);
        $this->assertStringContainsString("[1] HTTP method", $description);
        $this->assertMatchesRegularExpression(
            '/\[0\] path string[^\n]*\n\s*\[1\] HTTP method/',
            $description
        );
        $this->assertStringNotContainsString(
            "[0] path string, e.g. '/api/rooms/{room_id:.*}/files' [1] HTTP method",
            $description
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignmentSource
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignment
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::projectRelativePath
     */
    public function test_extract_assignment_source_reads_constant_definitions(): void
    {
        $source = CodegenProvenance::extractAssignmentSource(
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            'constantDefinitions'
        );

        $this->assertStringContainsString('$constantDefinitions = [', $source);
        $this->assertStringContainsString('MEME_FILE_UPLOAD_FORM_NAME', $source);
        $this->assertStringEndsWith(';', $source);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseFromFileContents
     */
    public function test_parse_returns_null_when_markers_missing(): void
    {
        $this->assertNull(
            CodegenProvenance::parseFromFileContents("// just a normal comment\n")
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\GeneratedArtifactsEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\GeneratedArtifactsEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::buildPayload
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::descriptionForCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignment
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignments
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::projectRelativePath
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::flushDescriptionBlock
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::normalizeFqcn
     */
    public function test_finder_returns_one_entry_per_generation_process(): void
    {
        $finder = new GeneratedArtifactsEntryTypeFinder();
        $this->assertSame('generated_artifacts', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $this->assertCount(7, $entries);

        $byOutput = [];
        foreach ($entries as $entry) {
            $this->assertArrayHasKey('name', $entry);
            $this->assertArrayHasKey('generator', $entry);
            $this->assertArrayHasKey('generator_callable', $entry);
            $this->assertArrayHasKey('output_file', $entry);
            $this->assertArrayHasKey('description', $entry);
            $this->assertArrayNotHasKey('sources', $entry);
            $this->assertArrayNotHasKey('mappings', $entry);
            $this->assertNotSame('', $entry['description']);
            $byOutput[$entry['output_file']] = $entry;
        }

        $this->assertArrayHasKey('app/public/tsx/generated/constants.tsx', $byOutput);
        $this->assertArrayHasKey('app/public/tsx/generated/types.tsx', $byOutput);
        $this->assertArrayHasKey('app/public/tsx/generated/api_routes.tsx', $byOutput);
        $this->assertArrayHasKey('app/public/tsx/generated/widget_panels.tsx', $byOutput);
        $this->assertArrayHasKey('src/Bristolian/Response/Typed/', $byOutput);
        $this->assertArrayHasKey('src/Bristolian/Database/', $byOutput);
        $this->assertArrayHasKey('src/Bristolian/Model/Generated/', $byOutput);

        $constantsEntry = $byOutput['app/public/tsx/generated/constants.tsx'];
        $this->assertArrayHasKey('detail', $constantsEntry);
        $this->assertStringContainsString('$constantDefinitions = [', $constantsEntry['detail']);
        $this->assertArrayHasKey('detail_source', $constantsEntry);
        $this->assertSame(
            'src/Bristolian/CliController/GenerateFiles.php',
            $constantsEntry['detail_source']['file']
        );
        $this->assertIsInt($constantsEntry['detail_source']['line-start']);
        $this->assertIsInt($constantsEntry['detail_source']['line-end']);
        $this->assertGreaterThan(
            $constantsEntry['detail_source']['line-start'],
            $constantsEntry['detail_source']['line-end']
        );

        $typesEntry = $byOutput['app/public/tsx/generated/types.tsx'];
        $this->assertArrayHasKey('detail', $typesEntry);
        $this->assertStringContainsString('$types = [', $typesEntry['detail']);
        $this->assertStringContainsString('$enums = [', $typesEntry['detail']);
        $this->assertArrayHasKey('detail_source', $typesEntry);
        $this->assertSame(
            'src/Bristolian/CliController/GenerateFiles.php',
            $typesEntry['detail_source']['file']
        );
        $this->assertGreaterThanOrEqual(
            $typesEntry['detail_source']['line-start'],
            $typesEntry['detail_source']['line-end']
        );

        $this->assertArrayNotHasKey('detail', $byOutput['app/public/tsx/generated/widget_panels.tsx']);
        $this->assertArrayNotHasKey('detail_source', $byOutput['app/public/tsx/generated/widget_panels.tsx']);
        $this->assertArrayNotHasKey('detail', $byOutput['src/Bristolian/Response/Typed/']);
        $this->assertArrayNotHasKey('detail_source', $byOutput['src/Bristolian/Response/Typed/']);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignment
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::projectRelativePath
     */
    public function test_extract_assignment_includes_source_map(): void
    {
        $assignment = CodegenProvenance::extractAssignment(
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            'constantDefinitions'
        );

        $this->assertStringContainsString('$constantDefinitions = [', $assignment['text']);
        $this->assertSame(
            'src/Bristolian/CliController/GenerateFiles.php',
            $assignment['file']
        );
        $this->assertGreaterThan(0, $assignment['line-start']);
        $this->assertGreaterThan($assignment['line-start'], $assignment['line-end']);

        $fileLines = file(
            dirname(__DIR__, 4) . '/' . $assignment['file']
        );
        $this->assertIsArray($fileLines);
        $snippet = implode(
            '',
            array_slice(
                $fileLines,
                $assignment['line-start'] - 1,
                $assignment['line-end'] - $assignment['line-start'] + 1
            )
        );
        $this->assertStringContainsString('$constantDefinitions = [', $snippet);
        $this->assertStringContainsString('MEME_FILE_UPLOAD_FORM_NAME', $snippet);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignments
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignment
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::projectRelativePath
     */
    public function test_extract_assignments_joins_multiple_variables(): void
    {
        $assignment = CodegenProvenance::extractAssignments(
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptTypes',
            ['types', 'enums']
        );

        $this->assertStringContainsString('$types = [', $assignment['detail']);
        $this->assertStringContainsString('$enums = [', $assignment['detail']);
        $this->assertSame(
            'src/Bristolian/CliController/GenerateFiles.php',
            $assignment['detail_source']['file']
        );
        $this->assertGreaterThan(0, $assignment['detail_source']['line-start']);
        $this->assertGreaterThanOrEqual(
            $assignment['detail_source']['line-start'],
            $assignment['detail_source']['line-end']
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::buildPayload
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::descriptionForCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::flushDescriptionBlock
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::normalizeFqcn
     */
    public function test_buildPayload_includes_detail_source(): void
    {
        $payload = CodegenProvenance::buildPayload(
            'generate:javascript_constants',
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            'app/public/tsx/generated/constants.tsx',
            '$constantDefinitions = [];',
            [
                'file' => 'src/Bristolian/CliController/GenerateFiles.php',
                'line-start' => 10,
                'line-end' => 20,
            ]
        );

        $this->assertSame(
            [
                'file' => 'src/Bristolian/CliController/GenerateFiles.php',
                'line-start' => 10,
                'line-end' => 20,
            ],
            $payload['detail_source']
        );
        $this->assertSame('$constantDefinitions = [];', $payload['detail']);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignment
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     */
    public function test_extract_assignment_throws_when_variable_missing(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not find assignment');
        CodegenProvenance::extractAssignment(
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            'thisVariableDoesNotExist'
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignments
     */
    public function test_extract_assignments_throws_when_variable_list_empty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('at least one variable name');
        CodegenProvenance::extractAssignments(
            'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            []
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::descriptionForCallable
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseCallable
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::normalizeFqcn
     */
    public function test_descriptionForCallable_rejects_invalid_callable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid generator callable');
        CodegenProvenance::descriptionForCallable('NotAValidCallable');
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::projectRelativePath
     */
    public function test_projectRelativePath_returns_normalized_absolute_when_outside_root(): void
    {
        $this->assertSame(
            '/tmp/outside/file.php',
            CodegenProvenance::projectRelativePath('/tmp/outside/file.php', '/var/app')
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseFromFileContents
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::formatLineComment
     */
    public function test_parse_reads_star_prefixed_comment_lines_and_detail_source(): void
    {
        $payload = [
            'generator' => 'generate:javascript_constants',
            'generator_callable' => 'Bristolian\\CliController\\GenerateFiles::generateJavaScriptConstants',
            'output_file' => 'app/public/tsx/generated/constants.tsx',
            'description' => 'Constants shared with TypeScript.',
            'detail' => '$constantDefinitions = [];',
            'detail_source' => [
                'file' => 'src/Bristolian/CliController/GenerateFiles.php',
                'line-start' => 10,
                'line-end' => 20,
            ],
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->assertIsString($json);

        $fileContents = "/*\n * " . CodegenProvenance::BEGIN_MARKER . "\n";
        foreach (explode("\n", $json) as $line) {
            $fileContents .= ' * ' . $line . "\n";
        }
        $fileContents .= ' * ' . CodegenProvenance::END_MARKER . "\n */\n";

        $parsed = CodegenProvenance::parseFromFileContents($fileContents);
        $this->assertIsArray($parsed);
        $this->assertSame($payload['generator'], $parsed['generator']);
        $this->assertSame($payload['detail'], $parsed['detail']);
        $this->assertSame($payload['detail_source'], $parsed['detail_source']);
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function provides_parseFromFileContents_invalid_payloads(): \Generator
    {
        $begin = CodegenProvenance::BEGIN_MARKER;
        $end = CodegenProvenance::END_MARKER;

        yield 'invalid json' => [
            "// {$begin}\n// {not-json\n// {$end}\n",
        ];
        yield 'missing required keys' => [
            "// {$begin}\n// {\"generator\": \"x\"}\n// {$end}\n",
        ];
        yield 'wrong value types' => [
            "// {$begin}\n// "
            . '{"generator":1,"generator_callable":"a","output_file":"b","description":"c"}'
            . "\n// {$end}\n",
        ];
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::parseFromFileContents
     */
    #[DataProvider('provides_parseFromFileContents_invalid_payloads')]
    public function test_parse_returns_null_for_invalid_payloads(string $contents): void
    {
        $this->assertNull(CodegenProvenance::parseFromFileContents($contents));
    }
}

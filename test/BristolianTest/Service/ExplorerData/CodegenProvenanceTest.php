<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\CodegenProvenance;
use Bristolian\Service\ExplorerData\GeneratedArtifactsEntryTypeFinder;
use BristolianTest\BaseTestCase;

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
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::descriptionForCallable
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
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignmentSource
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
     * @covers \Bristolian\Service\ExplorerData\CodegenProvenance::extractAssignments
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
}

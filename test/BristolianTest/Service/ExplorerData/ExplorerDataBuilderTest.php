<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\ExplorerDataBuilder;
use Bristolian\Service\ExplorerData\SupervisordTasksEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ExplorerDataBuilderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::__construct
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::getOutputPath
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::execute
     */
    public function test_execute_writes_root_entries_when_no_finders_added(): void
    {
        $outputPath = sys_get_temp_dir() . '/explorer-data-test-' . uniqid('', true) . '.json';

        $builder = new ExplorerDataBuilder($outputPath);
        $this->assertSame($outputPath, $builder->getOutputPath());

        $builder->execute();

        $this->assertFileExists($outputPath);

        $decoded = json_decode(file_get_contents($outputPath), true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('root', $decoded);
        $this->assertCount(9, $decoded['root']);
        $this->assertSame('CLI commands', $decoded['root'][0]['name']);
        $this->assertSame('/cli_commands', $decoded['root'][0]['path']);
        $this->assertSame('API endpoints', $decoded['root'][3]['name']);
        $this->assertSame('/api_endpoints', $decoded['root'][3]['path']);
        $this->assertSame('HTTP endpoints', $decoded['root'][4]['name']);
        $this->assertSame('/http_endpoints', $decoded['root'][4]['path']);
        $this->assertSame('Widgets', $decoded['root'][5]['name']);
        $this->assertSame('/widgets', $decoded['root'][5]['path']);
        $this->assertSame('Dependencies', $decoded['root'][6]['name']);
        $this->assertSame('/dependencies', $decoded['root'][6]['path']);
        $this->assertSame('Data Sources', $decoded['root'][7]['name']);
        $this->assertSame('/datasources', $decoded['root'][7]['path']);
        $this->assertSame('Generated artifacts', $decoded['root'][8]['name']);
        $this->assertSame('/generated_artifacts', $decoded['root'][8]['path']);

        $this->assertArrayHasKey('root_explanations', $decoded);
        $this->assertIsArray($decoded['root_explanations']);
        $this->assertNotEmpty($decoded['root_explanations']);

        $this->assertArrayHasKey('quality_tools', $decoded);
        $this->assertIsArray($decoded['quality_tools']);
        $this->assertNotEmpty($decoded['quality_tools']);
        $this->assertSame('phpstan', $decoded['quality_tools'][0]['id']);
        $this->assertStringContainsString('docker exec', $decoded['quality_tools'][0]['command']);
        $this->assertArrayHasKey('stage', $decoded['quality_tools'][0]);
        $this->assertNotEmpty($decoded['quality_tools'][0]['globs']);

        $widgetsExplanation = null;
        $dependenciesExplanation = null;
        $generatedArtifactsExplanation = null;
        $qualityToolsExplanation = null;
        foreach ($decoded['root_explanations'] as $explanation) {
            if ($explanation['path'] === '/widgets') {
                $widgetsExplanation = $explanation;
            }
            if ($explanation['path'] === '/dependencies') {
                $dependenciesExplanation = $explanation;
            }
            if ($explanation['path'] === '/generated_artifacts') {
                $generatedArtifactsExplanation = $explanation;
            }
            if ($explanation['path'] === 'quality_tools') {
                $qualityToolsExplanation = $explanation;
            }
        }

        $this->assertIsArray($widgetsExplanation);
        $this->assertSame('ui', $widgetsExplanation['role']);
        $this->assertArrayHasKey('description', $widgetsExplanation);
        $this->assertStringContainsString('WidgetRegistry', $widgetsExplanation['description']);

        $this->assertIsArray($dependenciesExplanation);
        $this->assertSame('dependency', $dependenciesExplanation['role']);
        $this->assertStringContainsString('Service contracts', $dependenciesExplanation['description']);

        $this->assertIsArray($generatedArtifactsExplanation);
        $this->assertSame('codegen', $generatedArtifactsExplanation['role']);
        $this->assertStringContainsString('one entry per auto-generation process', strtolower($generatedArtifactsExplanation['description']));
        $this->assertStringContainsString('generator_callable', $generatedArtifactsExplanation['description']);

        $this->assertIsArray($qualityToolsExplanation);
        $this->assertSame('workflow', $qualityToolsExplanation['role']);
        $this->assertStringContainsString('Glob rules', $qualityToolsExplanation['description']);
        $this->assertStringContainsString('stage', $qualityToolsExplanation['item_shape']);

        unlink($outputPath);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::addFromEntryTypeFinder
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::execute
     */
    public function test_execute_writes_supervisord_tasks_from_finder(): void
    {
        $outputPath = sys_get_temp_dir() . '/explorer-data-test-' . uniqid('', true) . '.json';

        $builder = new ExplorerDataBuilder($outputPath);
        $builder->addFromEntryTypeFinder(new SupervisordTasksEntryTypeFinder());
        $builder->execute();

        $decoded = json_decode(file_get_contents($outputPath), true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('root', $decoded);
        $this->assertCount(9, $decoded['root']);
        $this->assertArrayHasKey('root_explanations', $decoded);
        $this->assertArrayHasKey('supervisord_tasks', $decoded);
        $this->assertCount(5, $decoded['supervisord_tasks']);

        unlink($outputPath);
    }
}

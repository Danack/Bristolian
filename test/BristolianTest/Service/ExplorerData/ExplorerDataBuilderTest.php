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
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::buildJsonContent
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
        $this->assertSame('PHPStan', $decoded['quality_tools'][0]['label']);
        $this->assertStringContainsString('docker exec', $decoded['quality_tools'][0]['command']);
        $this->assertArrayNotHasKey('stage', $decoded['quality_tools'][0]);
        $this->assertArrayNotHasKey('globs', $decoded['quality_tools'][0]);
        $this->assertSame('codesniffer', $decoded['quality_tools'][1]['id']);
        $this->assertSame('unit_tests', $decoded['quality_tools'][2]['id']);
        $this->assertSame('behat', $decoded['quality_tools'][3]['id']);
        $this->assertSame('all_tests', $decoded['quality_tools'][4]['id']);

        $this->assertArrayHasKey('cached_tools', $decoded);
        $this->assertIsArray($decoded['cached_tools']);
        $this->assertNotEmpty($decoded['cached_tools']);
        $this->assertSame('php_test_coverage', $decoded['cached_tools'][0]['id']);
        $this->assertSame('Check PHP test coverage', $decoded['cached_tools'][0]['label']);
        $this->assertSame('report_missing_coverage.php', $decoded['cached_tools'][0]['tool_path']);
        $this->assertSame('bristolian-php_fpm-1', $decoded['cached_tools'][0]['container_name']);
        $this->assertIsArray($decoded['cached_tools'][0]['globs']);
        $this->assertStringContainsString('docker exec', $decoded['cached_tools'][0]['command']);

        $this->assertArrayHasKey('workflows', $decoded);
        $this->assertIsArray($decoded['workflows']);
        $this->assertNotEmpty($decoded['workflows']);
        $this->assertSame('work-on-selection', $decoded['workflows'][0]['id']);
        $this->assertSame('boot', $decoded['workflows'][0]['initial']);
        $this->assertSame(['work', 'qc', 'checkin'], $decoded['workflows'][0]['steps']);
        $this->assertArrayHasKey('boot', $decoded['workflows'][0]['states']);
        $this->assertArrayHasKey('dirtyChoice', $decoded['workflows'][0]['states']);
        $this->assertArrayHasKey('cleaningUp', $decoded['workflows'][0]['states']);
        $this->assertArrayHasKey('resumingWork', $decoded['workflows'][0]['states']);
        $this->assertSame(
            'CLEAN_UP',
            $decoded['workflows'][0]['states']['dirtyChoice']['ui']['chromeActions'][0]['event']
        );
        $this->assertSame(
            'RESUME',
            $decoded['workflows'][0]['states']['dirtyChoice']['ui']['chromeActions'][1]['event']
        );
        $this->assertSame('boot', $decoded['workflows'][0]['states']['checkin']['on']['PRIMARY']['target']);
        $this->assertFalse($decoded['workflows'][0]['states']['idle']['ui']['showCursorCommands']);

        $widgetsExplanation = null;
        $dependenciesExplanation = null;
        $generatedArtifactsExplanation = null;
        $qualityToolsExplanation = null;
        $cachedToolsExplanation = null;
        $cursorCommandsExplanation = null;
        $workflowsExplanation = null;
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
            if ($explanation['path'] === 'cached_tools') {
                $cachedToolsExplanation = $explanation;
            }
            if ($explanation['path'] === 'cursor_commands') {
                $cursorCommandsExplanation = $explanation;
            }
            if ($explanation['path'] === 'workflows') {
                $workflowsExplanation = $explanation;
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
        $this->assertStringContainsString('quality-control buttons', $qualityToolsExplanation['description']);
        $this->assertStringContainsString('id, label, command', $qualityToolsExplanation['item_shape']);
        $this->assertStringNotContainsString('globs', $qualityToolsExplanation['item_shape']);

        $this->assertIsArray($cachedToolsExplanation);
        $this->assertSame('workflow', $cachedToolsExplanation['role']);
        $this->assertStringContainsString('.output.json', $cachedToolsExplanation['description']);

        $this->assertIsArray($cursorCommandsExplanation);
        $this->assertSame('workflow', $cursorCommandsExplanation['role']);
        $this->assertStringContainsString('command.meta.json', $cursorCommandsExplanation['description']);
        $this->assertStringContainsString('priority', $cursorCommandsExplanation['item_shape']);

        $this->assertIsArray($workflowsExplanation);
        $this->assertSame('workflow', $workflowsExplanation['role']);
        $this->assertStringContainsString('Work on selection', $workflowsExplanation['description']);
        $this->assertStringContainsString('states', $workflowsExplanation['item_shape']);

        unlink($outputPath);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::addFromEntryTypeFinder
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::execute
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::buildJsonContent
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

    /**
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::__construct
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::execute
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::buildJsonContent
     */
    public function test_execute_creates_missing_output_directory(): void
    {
        $outputDirectory = sys_get_temp_dir() . '/explorer-data-dir-' . uniqid('', true);
        $outputPath = $outputDirectory . '/nested/codeview-data.json';

        $builder = new ExplorerDataBuilder($outputPath);
        $builder->execute();

        $this->assertDirectoryExists($outputDirectory . '/nested');
        $this->assertFileExists($outputPath);

        unlink($outputPath);
        rmdir($outputDirectory . '/nested');
        rmdir($outputDirectory);
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\CliController;

use Bristolian\CliController\GenerateExplorerData;
use Bristolian\Service\CliOutput\CapturingCliOutput;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class GenerateExplorerDataTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\CliController\GenerateExplorerData::__construct
     * @covers \Bristolian\CliController\GenerateExplorerData::generateExplorerData
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::__construct
     * @covers \Bristolian\Service\ExplorerData\ExplorerDataBuilder::execute
     */
    public function test_generateExplorerData_writes_supervisord_tasks(): void
    {
        $outputPath = dirname(__DIR__, 3) . '/codeview-data.json';

        if (file_exists($outputPath) === true) {
            unlink($outputPath);
        }

        $cliOutput = new CapturingCliOutput();
        $controller = new GenerateExplorerData($cliOutput);
        $controller->generateExplorerData();

        $this->assertFileExists($outputPath);

        $decoded = json_decode(file_get_contents($outputPath), true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('root', $decoded);
        $this->assertCount(6, $decoded['root']);
        $this->assertSame('Data Sources', $decoded['root'][5]['name']);
        $this->assertSame('/datasources', $decoded['root'][5]['path']);
        $this->assertArrayHasKey('cli_commands', $decoded);
        $this->assertArrayHasKey('code-map', $decoded);
        $this->assertArrayHasKey('controllers', $decoded);
        $this->assertArrayHasKey('datasources', $decoded);
        $this->assertArrayHasKey('dependencies', $decoded);
        $this->assertArrayHasKey('http_endpoints', $decoded);
        $this->assertArrayHasKey('api_endpoints', $decoded);
        $this->assertArrayHasKey('supervisord_tasks', $decoded);
        $this->assertNotEmpty($decoded['cli_commands']);
        $this->assertNotEmpty($decoded['code-map']);
        $this->assertNotEmpty($decoded['controllers']);
        $this->assertNotEmpty($decoded['datasources']);
        $this->assertNotEmpty($decoded['dependencies']);
        $this->assertNotEmpty($decoded['http_endpoints']);
        $this->assertNotEmpty($decoded['api_endpoints']);
        $this->assertCount(5, $decoded['supervisord_tasks']);

        $roomDatasource = null;
        foreach ($decoded['datasources'] as $datasource) {
            if ($datasource['name'] === 'room') {
                $roomDatasource = $datasource;
                break;
            }
        }
        $this->assertIsArray($roomDatasource);
        $this->assertSame('database', $roomDatasource['type']);
        $this->assertArrayNotHasKey('path', $roomDatasource);

        $roomRepo = null;
        foreach ($decoded['dependencies'] as $dependency) {
            if ($dependency['name'] === 'Bristolian\\Repo\\RoomRepo\\RoomRepo') {
                $roomRepo = $dependency;
                break;
            }
        }
        $this->assertIsArray($roomRepo);
        $this->assertArrayNotHasKey('datasources', $roomRepo);
        $this->assertNotEmpty($roomRepo['methods']);

        $createRoom = null;
        foreach ($roomRepo['methods'] as $methodEntry) {
            if ($methodEntry['name'] === 'createRoom') {
                $createRoom = $methodEntry;
                break;
            }
        }
        $this->assertIsArray($createRoom);
        $this->assertContains(
            [
                'path' => '/datasources/room',
                'reads' => false,
                'writes' => true,
            ],
            $createRoom['datasources']
        );

        $this->assertStringContainsString('Wrote codeview data', $cliOutput->getCapturedOutput());
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\DatasourcesEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class DatasourcesEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\DatasourcesEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\DatasourcesEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::datasourcePathForTableName
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::findDatasourceEntries
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::findAnnotatedInterfaceEntries
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::findImplementationsForInterface
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::findRepoInterfaceClassNames
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::collectMethodDatasourceEntries
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::aggregateDatasourceUsages
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::tableNameFromHelperClass
     */
    public function test_findEntries_lists_unique_database_datasources(): void
    {
        $finder = new DatasourcesEntryTypeFinder();

        $this->assertSame('datasources', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $this->assertGreaterThan(10, count($entries));

        $roomDatasource = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'room') {
                $roomDatasource = $entry;
                break;
            }
        }

        $this->assertIsArray($roomDatasource);
        $this->assertSame(['name', 'type'], array_keys($roomDatasource));
        $this->assertSame('database', $roomDatasource['type']);

        $names = [];
        foreach ($entries as $entry) {
            $names[] = $entry['name'];
        }
        $this->assertSame($names, array_values(array_unique($names)));
        $sortedNames = $names;
        sort($sortedNames);
        $this->assertSame($sortedNames, $names);
    }
}

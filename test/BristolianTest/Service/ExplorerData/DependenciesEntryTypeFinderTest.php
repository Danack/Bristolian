<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\DependenciesEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class DependenciesEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\DependenciesEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\DependenciesEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::datasourcePathForTableName
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::findAnnotatedInterfaceEntries
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::findImplementationsForInterface
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::findRepoInterfaceClassNames
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::collectMethodDatasourceEntries
     * @covers \Bristolian\Service\ExplorerData\RepoInterfaceImplementationDiscovery::tableNameFromHelperClass
     * @covers \Bristolian\Service\ExplorerData\ServiceDependencyDiscovery::findEntries
     * @covers \Bristolian\Service\ExplorerData\ServiceDependencyDiscovery::shouldSkipNamespace
     * @covers \Bristolian\Service\ExplorerData\ServiceDependencyDiscovery::implementsListedServiceInterface
     * @covers \Bristolian\Service\ExplorerData\ServiceDependencyDiscovery::looksLikeValueOrErrorType
     * @covers \Bristolian\Service\ExplorerData\ServiceDependencyDiscovery::findSubclassesInPackage
     * @covers \Bristolian\Service\ExplorerData\PhpClassUnderDirectoryDiscovery::findClassNames
     * @covers \Bristolian\Service\ExplorerData\PhpClassUnderDirectoryDiscovery::filterLoadableTypes
     */
    public function test_findEntries_lists_repo_interfaces_with_method_datasource_paths(): void
    {
        $finder = new DependenciesEntryTypeFinder();

        $this->assertSame('dependencies', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $this->assertGreaterThan(10, count($entries));

        $roomRepo = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\Repo\\RoomRepo\\RoomRepo') {
                $roomRepo = $entry;
                break;
            }
        }

        $this->assertIsArray($roomRepo);
        $this->assertSame(['name', 'implementations', 'methods'], array_keys($roomRepo));
        $this->assertSame(
            [
                'Bristolian\\Repo\\RoomRepo\\FakeRoomRepo',
                'Bristolian\\Repo\\RoomRepo\\PdoRoomRepo',
            ],
            $roomRepo['implementations']
        );
        $this->assertArrayNotHasKey('datasources', $roomRepo);
        $this->assertNotEmpty($roomRepo['methods']);
        $methodNames = array_column($roomRepo['methods'], 'name');
        $this->assertContains('createRoom', $methodNames);

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

        $roomVideoRepo = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\Repo\\RoomVideoRepo\\RoomVideoRepo') {
                $roomVideoRepo = $entry;
                break;
            }
        }

        $this->assertIsArray($roomVideoRepo);
        $this->assertArrayNotHasKey('datasources', $roomVideoRepo);

        $methodDatasourcePaths = [];
        foreach ($roomVideoRepo['methods'] as $methodEntry) {
            foreach ($methodEntry['datasources'] as $datasource) {
                $methodDatasourcePaths[$datasource['path']] = true;
            }
        }
        $this->assertArrayHasKey('/datasources/room_video', $methodDatasourcePaths);
        $this->assertArrayHasKey('/datasources/room_tag', $methodDatasourcePaths);

        $webPushService = null;
        $openFoodFactsFetcher = null;
        $requestNonce = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\Service\\WebPushService\\WebPushService') {
                $webPushService = $entry;
            }
            if ($entry['name'] === 'Bristolian\\Service\\TinnedFish\\OpenFoodFactsFetcher') {
                $openFoodFactsFetcher = $entry;
            }
            if ($entry['name'] === 'Bristolian\\Service\\RequestNonce') {
                $requestNonce = $entry;
            }
        }

        $this->assertIsArray($webPushService);
        $this->assertSame(['name', 'implementations', 'methods'], array_keys($webPushService));
        $this->assertContains(
            'Bristolian\\Service\\WebPushService\\StandardWebPushService',
            $webPushService['implementations']
        );
        $this->assertSame(
            ['Bristolian\\Service\\WebPushService\\StandardWebPushService'],
            $webPushService['implementations']
        );
        $this->assertSame([], $webPushService['methods']);

        $this->assertIsArray($openFoodFactsFetcher);
        $this->assertContains(
            'Bristolian\\Service\\TinnedFish\\FakeOpenFoodFactsFetcher',
            $openFoodFactsFetcher['implementations']
        );
        $this->assertSame([], $openFoodFactsFetcher['methods']);

        $this->assertIsArray($requestNonce);
        $this->assertSame([], $requestNonce['implementations']);
        $this->assertSame([], $requestNonce['methods']);

        foreach ($entries as $entry) {
            $this->assertStringStartsNotWith(
                'Bristolian\\Service\\ExplorerData\\',
                $entry['name']
            );
        }
    }
}

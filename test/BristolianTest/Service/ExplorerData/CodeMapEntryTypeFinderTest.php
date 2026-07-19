<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\CodeMapEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CodeMapEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\CodeMapEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\CodeMapEntryTypeFinder::findEntries
     */
    public function test_findEntries_maps_cli_http_and_api_controllers(): void
    {
        $finder = new CodeMapEntryTypeFinder();

        $this->assertSame('code-map', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();

        $this->assertGreaterThan(80, count($entries));

        $helloEntry = null;
        $pagesIndexEntry = null;
        $bristolStairsGetDataEntry = null;
        $roomRepoInterfaceEntry = null;
        $fakeRoomRepoEntry = null;
        $pdoRoomRepoEntry = null;

        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\CliController\\Debug::hello') {
                $helloEntry = $entry;
            }
            if ($entry['name'] === 'Bristolian\\AppController\\Pages::index') {
                $pagesIndexEntry = $entry;
            }
            if ($entry['name'] === 'Bristolian\\AppController\\BristolStairs::getData') {
                $bristolStairsGetDataEntry = $entry;
            }
            if ($entry['name'] === 'Bristolian\\Repo\\RoomRepo\\RoomRepo') {
                $roomRepoInterfaceEntry = $entry;
            }
            if ($entry['name'] === 'Bristolian\\Repo\\RoomRepo\\FakeRoomRepo') {
                $fakeRoomRepoEntry = $entry;
            }
            if ($entry['name'] === 'Bristolian\\Repo\\RoomRepo\\PdoRoomRepo') {
                $pdoRoomRepoEntry = $entry;
            }
        }

        $this->assertIsArray($helloEntry);
        $this->assertSame(['name', 'file', 'line-start', 'line-end', 'dependencies'], array_keys($helloEntry));
        $this->assertSame('src/Bristolian/CliController/Debug.php', $helloEntry['file']);
        $this->assertSame([], $helloEntry['dependencies']);

        $this->assertIsArray($pagesIndexEntry);
        $this->assertSame('src/Bristolian/AppController/Pages.php', $pagesIndexEntry['file']);

        $this->assertIsArray($bristolStairsGetDataEntry);
        $this->assertSame('src/Bristolian/AppController/BristolStairs.php', $bristolStairsGetDataEntry['file']);
        $this->assertContains(
            'Bristolian\\Repo\\BristolStairsRepo\\BristolStairsRepo',
            $bristolStairsGetDataEntry['dependencies']
        );

        $this->assertIsArray($roomRepoInterfaceEntry);
        $this->assertSame(
            'src/Bristolian/Repo/RoomRepo/RoomRepo.php',
            $roomRepoInterfaceEntry['file']
        );
        $this->assertSame([], $roomRepoInterfaceEntry['dependencies']);

        $this->assertIsArray($fakeRoomRepoEntry);
        $this->assertSame(
            'src/Bristolian/Repo/RoomRepo/FakeRoomRepo.php',
            $fakeRoomRepoEntry['file']
        );

        $this->assertIsArray($pdoRoomRepoEntry);
        $this->assertSame(
            'src/Bristolian/Repo/RoomRepo/PdoRoomRepo.php',
            $pdoRoomRepoEntry['file']
        );

        $webPushServiceEntry = null;
        $standardWebPushServiceEntry = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\Service\\WebPushService\\WebPushService') {
                $webPushServiceEntry = $entry;
            }
            if ($entry['name'] === 'Bristolian\\Service\\WebPushService\\StandardWebPushService') {
                $standardWebPushServiceEntry = $entry;
            }
        }

        $this->assertIsArray($webPushServiceEntry);
        $this->assertSame(
            'src/Bristolian/Service/WebPushService/WebPushService.php',
            $webPushServiceEntry['file']
        );
        $this->assertIsArray($standardWebPushServiceEntry);
        $this->assertSame(
            'src/Bristolian/Service/WebPushService/StandardWebPushService.php',
            $standardWebPushServiceEntry['file']
        );
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Cli\CliCommandRegistry;
use Bristolian\Service\ExplorerData\CliCommandsEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CliCommandsEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\CliCommandsEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\CliCommandsEntryTypeFinder::findEntries
     */
    public function test_findEntries_lists_all_registered_commands(): void
    {
        $finder = new CliCommandsEntryTypeFinder();

        $this->assertSame('cli_commands', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $registryCount = count(CliCommandRegistry::getAllDefinitions());

        $this->assertCount($registryCount, $entries);

        $helloEntry = null;
        foreach ($entries as $entry) {
            if ($entry['command'] === 'debug:hello') {
                $helloEntry = $entry;
                break;
            }
        }

        $this->assertIsArray($helloEntry);
        $this->assertSame(['command', 'controller', 'description'], array_keys($helloEntry));
        $this->assertSame('Bristolian\CliController\Debug::hello', $helloEntry['controller']);
        $this->assertSame('Test cli commands are working.', $helloEntry['description']);
    }
}

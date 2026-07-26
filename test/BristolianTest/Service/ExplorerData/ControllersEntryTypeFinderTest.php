<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\ControllersEntryTypeFinder;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ControllersEntryTypeFinderTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\ControllersEntryTypeFinder::getEntryTypeKey
     * @covers \Bristolian\Service\ExplorerData\ControllersEntryTypeFinder::findEntries
     * @covers \Bristolian\Service\ExplorerData\ControllersEntryTypeFinder::collectControllerCallables
     * @covers \Bristolian\Service\ExplorerData\ControllersEntryTypeFinder::collectDependenciesForClass
     * @covers \Bristolian\Service\ExplorerData\ControllersEntryTypeFinder::classTypeNamesFromParameters
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::parseControllerCallable
     */
    public function test_findEntries_lists_controller_classes_with_dependencies(): void
    {
        $finder = new ControllersEntryTypeFinder();

        $this->assertSame('controllers', $finder->getEntryTypeKey());

        $entries = $finder->findEntries();
        $this->assertGreaterThan(10, count($entries));

        $whatDoTheyKnowController = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\CliController\\WhatDoTheyKnowFeedCliController') {
                $whatDoTheyKnowController = $entry;
                break;
            }
        }

        $this->assertIsArray($whatDoTheyKnowController);
        $this->assertSame(['name', 'dependencies'], array_keys($whatDoTheyKnowController));
        $this->assertContains(
            'Bristolian\\Repo\\WhatDoTheyKnowRequestEventRepo\\WhatDoTheyKnowRequestEventRepo',
            $whatDoTheyKnowController['dependencies']
        );
        $this->assertContains(
            'Bristolian\\Service\\WhatDoTheyKnowFeedFetcher\\WhatDoTheyKnowFeedFetcher',
            $whatDoTheyKnowController['dependencies']
        );
        $this->assertContains(
            'Bristolian\\Repo\\RoomRepo\\RoomRepo',
            $whatDoTheyKnowController['dependencies']
        );

        $emailController = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\CliController\\Email') {
                $emailController = $entry;
                break;
            }
        }

        $this->assertIsArray($emailController);
        $this->assertContains(
            'Bristolian\\Repo\\EmailQueue\\EmailQueue',
            $emailController['dependencies']
        );
        $this->assertContains(
            'Bristolian\\Service\\EmailSender\\EmailClient',
            $emailController['dependencies']
        );

        $pagesController = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\AppController\\Pages') {
                $pagesController = $entry;
                break;
            }
        }

        $this->assertIsArray($pagesController);

        $bristolStairsController = null;
        foreach ($entries as $entry) {
            if ($entry['name'] === 'Bristolian\\AppController\\BristolStairs') {
                $bristolStairsController = $entry;
                break;
            }
        }

        $this->assertIsArray($bristolStairsController);
        $this->assertContains(
            'Bristolian\\Repo\\BristolStairsRepo\\BristolStairsRepo',
            $bristolStairsController['dependencies']
        );
    }
}

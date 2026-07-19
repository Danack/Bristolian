<?php

declare(strict_types=1);

namespace BristolianTest\Service\ExplorerData;

use Bristolian\Service\ExplorerData\ControllerCallableCodeLocator;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ControllerCallableCodeLocatorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::normalizeFqcn
     */
    public function test_normalizeFqcn_strips_leading_backslash(): void
    {
        $this->assertSame(
            'Bristolian\\AppController\\Rooms::getTags',
            ControllerCallableCodeLocator::normalizeFqcn('\\Bristolian\\AppController\\Rooms::getTags')
        );
        $this->assertSame(
            'Bristolian\\AppController\\Rooms::getTags',
            ControllerCallableCodeLocator::normalizeFqcn('Bristolian\\AppController\\Rooms::getTags')
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::locate
     */
    public function test_locate_resolves_debug_hello_method(): void
    {
        $projectRoot = dirname(__DIR__, 4);

        $location = ControllerCallableCodeLocator::locate(
            'Bristolian\\CliController\\Debug::hello',
            $projectRoot
        );

        $this->assertSame('Bristolian\\CliController\\Debug::hello', $location['name']);
        $this->assertSame('src/Bristolian/CliController/Debug.php', $location['file']);
        $this->assertSame(47, $location['line-start']);
        $this->assertSame(50, $location['line-end']);
        $this->assertSame([], $location['dependencies']);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::locate
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::collectDependenciesForCallable
     */
    public function test_locate_includes_method_dependencies(): void
    {
        $projectRoot = dirname(__DIR__, 4);

        $location = ControllerCallableCodeLocator::locate(
            'Bristolian\\AppController\\BristolStairs::getData',
            $projectRoot
        );

        $this->assertSame('Bristolian\\AppController\\BristolStairs::getData', $location['name']);
        $this->assertSame('src/Bristolian/AppController/BristolStairs.php', $location['file']);
        $this->assertContains(
            'Bristolian\\Repo\\BristolStairsRepo\\BristolStairsRepo',
            $location['dependencies']
        );
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::locate
     */
    public function test_locate_accepts_leading_backslash_on_class_name(): void
    {
        $projectRoot = dirname(__DIR__, 4);

        $location = ControllerCallableCodeLocator::locate(
            '\\Bristolian\\CliController\\Debug::hello',
            $projectRoot
        );

        $this->assertSame('Bristolian\\CliController\\Debug::hello', $location['name']);
        $this->assertSame('src/Bristolian/CliController/Debug.php', $location['file']);
        $this->assertGreaterThan(0, $location['line-start']);
        $this->assertGreaterThanOrEqual($location['line-start'], $location['line-end']);
        $this->assertArrayHasKey('dependencies', $location);
    }

    /**
     * @covers \Bristolian\Service\ExplorerData\ControllerCallableCodeLocator::locateClass
     */
    public function test_locateClass_resolves_repo_interface(): void
    {
        $projectRoot = dirname(__DIR__, 4);

        $location = ControllerCallableCodeLocator::locateClass(
            'Bristolian\\Repo\\RoomRepo\\RoomRepo',
            $projectRoot
        );

        $this->assertSame('Bristolian\\Repo\\RoomRepo\\RoomRepo', $location['name']);
        $this->assertSame('src/Bristolian/Repo/RoomRepo/RoomRepo.php', $location['file']);
        $this->assertGreaterThan(0, $location['line-start']);
        $this->assertGreaterThanOrEqual($location['line-start'], $location['line-end']);
        $this->assertSame([], $location['dependencies']);
    }
}

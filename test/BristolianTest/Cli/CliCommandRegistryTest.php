<?php

declare(strict_types=1);

namespace BristolianTest\Cli;

use Bristolian\Cli\CliCommandRegistry;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CliCommandRegistryTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Cli\CliCommandRegistry::getDebugCommandDefinitions
     */
    public function test_getDebugCommandDefinitions_includes_hello_and_send_webpush(): void
    {
        $definitions = CliCommandRegistry::getDebugCommandDefinitions();
        $commandNames = array_map(
            static fn ($definition) => $definition->commandName,
            $definitions
        );

        $this->assertContains('debug:hello', $commandNames);
        $this->assertContains('debug:send_webpush', $commandNames);
    }

    /**
     * @covers \Bristolian\Cli\CliCommandRegistry::getAllDefinitions
     */
    public function test_getAllDefinitions_has_unique_command_names(): void
    {
        $definitions = CliCommandRegistry::getAllDefinitions();
        $commandNames = array_map(
            static fn ($definition) => $definition->commandName,
            $definitions
        );

        $this->assertGreaterThan(40, count($commandNames));
        $this->assertSame(count($commandNames), count(array_unique($commandNames)));
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\Cli;

use Bristolian\Cli\CliCommandDefinition;
use Bristolian\Cli\CliCommandRegistry;
use BristolianTest\BaseTestCase;
use Danack\Console\Application;
use Danack\Console\Command\Command;
use Danack\Console\Input\InputArgument;

/**
 * @coversNothing
 */
class CliCommandRegistryTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Cli\CliCommandDefinition::__construct
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
     * @covers \Bristolian\Cli\CliCommandDefinition::__construct
     * @covers \Bristolian\Cli\CliCommandRegistry::getAllDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getDebugCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getSeedCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getDatabaseCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getAdminAccountCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMiscCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getTestCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getRoomCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getEmailCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getGenerateCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getBristolStairsCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMemeCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getOpenApiCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMoonCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getBccTroCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getWhatDoTheyKnowCommandDefinitions
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

    /**
     * @covers \Bristolian\Cli\CliCommandRegistry::getControllerCallableForCommandName
     * @covers \Bristolian\Cli\CliCommandRegistry::getAllDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getDebugCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getSeedCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getDatabaseCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getAdminAccountCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMiscCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getTestCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getRoomCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getEmailCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getGenerateCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getBristolStairsCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMemeCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getOpenApiCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMoonCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getBccTroCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getWhatDoTheyKnowCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandDefinition::__construct
     */
    public function test_getControllerCallableForCommandName_returns_callable_or_null(): void
    {
        $this->assertSame(
            'Bristolian\CliController\Debug::hello',
            CliCommandRegistry::getControllerCallableForCommandName('debug:hello')
        );
        $this->assertNull(
            CliCommandRegistry::getControllerCallableForCommandName('definitely:not-a-command')
        );
    }

    /**
     * @covers \Bristolian\Cli\CliCommandRegistry::registerCommands
     * @covers \Bristolian\Cli\CliCommandRegistry::registerCommand
     * @covers \Bristolian\Cli\CliCommandRegistry::getAllDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getDebugCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getSeedCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getDatabaseCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getAdminAccountCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMiscCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getTestCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getRoomCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getEmailCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getGenerateCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getBristolStairsCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMemeCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getOpenApiCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getMoonCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getBccTroCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandRegistry::getWhatDoTheyKnowCommandDefinitions
     * @covers \Bristolian\Cli\CliCommandDefinition::__construct
     */
    public function test_registerCommands_registers_all_definitions_on_application(): void
    {
        $console = new Application();
        $definitions = CliCommandRegistry::getAllDefinitions();

        CliCommandRegistry::registerCommands($console, $definitions);

        foreach ($definitions as $definition) {
            $this->assertTrue(
                $console->has($definition->commandName),
                'Expected command to be registered: ' . $definition->commandName
            );
        }

        $sendWebPush = $console->get('debug:send_webpush');
        $this->assertInstanceOf(Command::class, $sendWebPush);
        $this->assertSame(
            'Send a webpush to a user, if they are registered for webpushes',
            $sendWebPush->getDescription()
        );
        $this->assertTrue($sendWebPush->getDefinition()->hasArgument('email_address'));
        $this->assertTrue($sendWebPush->getDefinition()->hasArgument('message'));
    }

    /**
     * @covers \Bristolian\Cli\CliCommandRegistry::registerCommand
     * @covers \Bristolian\Cli\CliCommandDefinition::__construct
     */
    public function test_registerCommand_allows_empty_description_and_configure(): void
    {
        $console = new Application();
        $definition = new CliCommandDefinition(
            'test:minimal',
            'Bristolian\CliController\Debug::hello'
        );

        CliCommandRegistry::registerCommand($console, $definition);

        $this->assertTrue($console->has('test:minimal'));
        $command = $console->get('test:minimal');
        $this->assertTrue(
            $command->getDescription() === null || $command->getDescription() === '',
            'Expected empty/null description when none was set'
        );
    }

    /**
     * @covers \Bristolian\Cli\CliCommandRegistry::registerCommand
     * @covers \Bristolian\Cli\CliCommandDefinition::__construct
     */
    public function test_registerCommand_runs_configure_callback(): void
    {
        $console = new Application();
        $definition = new CliCommandDefinition(
            'test:configured',
            'Bristolian\CliController\Debug::hello',
            'Configured test command',
            static function (Command $command): void {
                $command->addArgument('value', InputArgument::REQUIRED, 'A required value');
            }
        );

        CliCommandRegistry::registerCommand($console, $definition);

        $command = $console->get('test:configured');
        $this->assertSame('Configured test command', $command->getDescription());
        $this->assertTrue($command->getDefinition()->hasArgument('value'));
    }
}

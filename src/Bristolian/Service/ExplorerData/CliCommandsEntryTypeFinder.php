<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

use Bristolian\Cli\CliCommandRegistry;

class CliCommandsEntryTypeFinder implements EntryTypeFinder
{
    public function getEntryTypeKey(): string
    {
        return 'cli_commands';
    }

    public function findEntries(): array
    {
        $entries = [];

        foreach (CliCommandRegistry::getAllDefinitions() as $definition) {
            $entries[] = [
                'command' => $definition->commandName,
                'controller' => ControllerCallableCodeLocator::normalizeFqcn(
                    $definition->controllerCallable
                ),
                'description' => $definition->description,
            ];
        }

        return $entries;
    }
}

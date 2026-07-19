<?php

declare(strict_types=1);

namespace Bristolian\Cli;

use Danack\Console\Command\Command;

/**
 * Registered CLI command: name, controller callable, and optional Command setup.
 */
final readonly class CliCommandDefinition
{
    /**
     * @param callable(Command): void|null $configure
     */
    public function __construct(
        public string $commandName,
        public string $controllerCallable,
        public string $description = '',
        public mixed $configure = null,
    ) {
    }
}

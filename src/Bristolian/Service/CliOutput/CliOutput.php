<?php

declare(strict_types=1);

namespace Bristolian\Service\CliOutput;

/**
 * Abstraction for CLI output and process exit.
 * Production implementation echoes and exits; test implementation captures lines and throws on exit.
 */
interface CliOutput
{
    public function write(string $message): void;

    /**
     * Request the process to exit with the given code.
     * Production: exits the process. Test: throws CliExitRequestedException.
     */
    public function exit(int $code): never;
}

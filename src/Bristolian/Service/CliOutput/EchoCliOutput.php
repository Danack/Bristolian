<?php

declare(strict_types=1);

namespace Bristolian\Service\CliOutput;

/**
 * Production CLI output: writes to stdout and exits the process on exit().
 */
class EchoCliOutput implements CliOutput
{
    public function write(string $message): void
    {
        echo $message;
    }

    public function exit(int $code): never
    {
        exit($code);
    }
}

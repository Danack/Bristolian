<?php

declare(strict_types=1);

namespace Bristolian\Service\CliOutput;

/**
 * Thrown by CapturingCliOutput::exit() so tests can assert exit was requested without terminating.
 */
class CliExitRequestedException extends \Exception
{
    public function __construct(
        private int $exitCode,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message ?: "CLI exit requested with code $exitCode", $code, $previous);
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}

<?php

declare(strict_types=1);

namespace Bristolian\Service\CliOutput;

/**
 * Test CLI output: captures written lines and throws CliExitRequestedException on exit().
 */
class CapturingCliOutput implements CliOutput
{
    /** @var string[] */
    private array $lines = [];

    /** @var string[] */
    private array $errorLines = [];

    public function write(string $message): void
    {
        $this->lines[] = $message;
    }

    public function writeError(string $message): void
    {
        $this->errorLines[] = $message;
    }

    /**
     * @return string[] All messages passed to write (in order).
     */
    public function getCapturedLines(): array
    {
        return $this->lines;
    }

    /**
     * Full captured output as a single string (no separators between lines).
     */
    public function getCapturedOutput(): string
    {
        return implode('', $this->lines);
    }

    /**
     * @return string[] All messages passed to writeError (in order).
     */
    public function getCapturedErrorLines(): array
    {
        return $this->errorLines;
    }

    public function exit(int $code): never
    {
        throw new CliExitRequestedException($code);
    }
}

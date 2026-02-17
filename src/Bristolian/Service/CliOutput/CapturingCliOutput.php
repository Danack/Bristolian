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

    public function write(string $message): void
    {
        $this->lines[] = $message;
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

    public function exit(int $code): never
    {
        throw new CliExitRequestedException($code);
    }
}

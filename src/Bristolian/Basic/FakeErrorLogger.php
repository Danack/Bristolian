<?php

namespace Bristolian\Basic;

class FakeErrorLogger implements ErrorLogger
{
    /**
     * @var string[]
     */
    private array $log_lines = [];

    public function log(string $string): void
    {
        $this->log_lines[] = $string;
    }

    /**
     * @return string[]
     */
    public function getLogLines(): array
    {
        return $this->log_lines;
    }
}

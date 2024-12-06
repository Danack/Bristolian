<?php

namespace Bristolian\Basic;

class FakeErrorLogger implements ErrorLogger
{
    private array $log_lines = [];

    public function log(string $string): void
    {
        $this->log_lines[] = $string;
    }

    /**
     * @return array
     */
    public function getLogLines(): array
    {
        return $this->log_lines;
    }
}

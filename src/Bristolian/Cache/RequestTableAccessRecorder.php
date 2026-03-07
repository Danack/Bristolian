<?php

declare(strict_types=1);

namespace Bristolian\Cache;

class RequestTableAccessRecorder implements TableAccessRecorder
{
    /** @var array<string, true> */
    private array $tablesRead = [];

    /** @var array<string, true> */
    private array $tablesWritten = [];

    /**
     * @param string[] $tables
     */
    public function recordTablesRead(array $tables): void
    {
        foreach ($tables as $table) {
            $this->tablesRead[$table] = true;
        }
    }

    /**
     * @param string[] $tables
     */
    public function recordTablesWritten(array $tables): void
    {
        foreach ($tables as $table) {
            $this->tablesWritten[$table] = true;
        }
    }

    public function getTagsForResponse(): string
    {
        if (count($this->tablesRead) === 0) {
            return '';
        }

        $tags = [];
        foreach (array_keys($this->tablesRead) as $table) {
            $tags[] = 'table:' . $table;
        }

        return implode(',', $tags);
    }

    /**
     * @return string[]
     */
    public function getTablesWritten(): array
    {
        return array_keys($this->tablesWritten);
    }

    public function clear(): void
    {
        $this->tablesRead = [];
        $this->tablesWritten = [];
    }
}

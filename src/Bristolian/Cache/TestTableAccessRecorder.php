<?php

declare(strict_types=1);

namespace Bristolian\Cache;

/**
 * Test double that records every call so tests can assert which
 * tables were read/written and what tags would be produced.
 */
class TestTableAccessRecorder implements TableAccessRecorder
{
    /** @var array<int, string[]> */
    private array $recordedReads = [];

    /** @var array<int, string[]> */
    private array $recordedWrites = [];

    private RequestTableAccessRecorder $inner;

    public function __construct()
    {
        $this->inner = new RequestTableAccessRecorder();
    }

    /**
     * @param string[] $tables
     */
    public function recordTablesRead(array $tables): void
    {
        $this->recordedReads[] = $tables;
        $this->inner->recordTablesRead($tables);
    }

    /**
     * @param string[] $tables
     */
    public function recordTablesWritten(array $tables): void
    {
        $this->recordedWrites[] = $tables;
        $this->inner->recordTablesWritten($tables);
    }

    public function getTagsForResponse(): string
    {
        return $this->inner->getTagsForResponse();
    }

    /**
     * @return string[]
     */
    public function getTablesWritten(): array
    {
        return $this->inner->getTablesWritten();
    }

    public function clear(): void
    {
        $this->recordedReads = [];
        $this->recordedWrites = [];
        $this->inner->clear();
    }

    /**
     * @return array<int, string[]>
     */
    public function getRecordedReads(): array
    {
        return $this->recordedReads;
    }

    /**
     * @return array<int, string[]>
     */
    public function getRecordedWrites(): array
    {
        return $this->recordedWrites;
    }
}

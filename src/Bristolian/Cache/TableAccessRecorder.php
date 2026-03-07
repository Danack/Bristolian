<?php

declare(strict_types=1);

namespace Bristolian\Cache;

interface TableAccessRecorder
{
    /**
     * @param string[] $tables
     */
    public function recordTablesRead(array $tables): void;

    /**
     * @param string[] $tables
     */
    public function recordTablesWritten(array $tables): void;

    /**
     * Returns comma-separated cache tags (e.g. "table:room,table:bristol_stair_info")
     * for all tables read during this request.
     */
    public function getTagsForResponse(): string;

    /**
     * @return string[] Table names written during this request.
     */
    public function getTablesWritten(): array;

    public function clear(): void;
}

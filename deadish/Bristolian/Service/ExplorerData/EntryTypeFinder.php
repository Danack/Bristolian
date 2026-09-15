<?php

declare(strict_types=1);

namespace Bristolian\Service\ExplorerData;

interface EntryTypeFinder
{
    /**
     * Key used in the generated explorer JSON (e.g. "supervisord_tasks").
     */
    public function getEntryTypeKey(): string;

    /**
     * @return list<array<string, mixed>>
     */
    public function findEntries(): array;
}

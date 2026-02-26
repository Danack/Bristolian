<?php

declare(strict_types=1);

namespace BristolianChat\RoomMessagesWatcher;

/**
 * Fake implementation for tests. Configure initial max id and a queue of rows
 * to return from getNextChatMessageRowAfter (each call returns the next row, then null).
 */
class FakeRoomMessagesWatcher implements RoomMessagesWatcher
{
    /** @var array<int, array<string, mixed>> */
    private array $nextRowsQueue = [];

    private int|null $maxId = null;

    public function __construct(
        int|null $maxChatMessageId = null,
        array $nextRows = [],
    ) {
        $this->maxId = $maxChatMessageId;
        $this->nextRowsQueue = array_values($nextRows);
    }

    public function getInitialPreviousId(): int
    {
        return $this->maxId ?? 0;
    }

    public function getNextChatMessageRowAfter(int $previousId): array|null
    {
        $row = array_shift($this->nextRowsQueue);
        return $row;
    }
}

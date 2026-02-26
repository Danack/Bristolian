<?php

declare(strict_types=1);

namespace BristolianChat\RoomMessagesWatcher;

use Bristolian\Model\Generated\ChatMessage;

/**
 * Fake implementation for tests. Configure initial max id and a queue of rows
 * to return from getNextChatMessageAfter (each call returns the next message, then null).
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

    public function getNextChatMessageAfter(int $previousId): ChatMessage|null
    {
        $row = array_shift($this->nextRowsQueue);
        if ($row === null) {
            return null;
        }
        if (is_string($row['created_at'] ?? null)) {
            $row['created_at'] = new \DateTimeImmutable($row['created_at']);
        }
        return ChatMessage::fromArray($row);
    }
}

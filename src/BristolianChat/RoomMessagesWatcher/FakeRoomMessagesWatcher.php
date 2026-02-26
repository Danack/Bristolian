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
        return self::rowToChatMessage($row);
    }

    /**
     * @param array{id: int|string, text: string, user_id: string, room_id: string, reply_message_id: int|null|string, created_at: string|\DateTimeInterface} $row
     */
    private static function rowToChatMessage(array $row): ChatMessage
    {
        $created_at = $row['created_at'] ?? null;
        if (is_string($created_at)) {
            $created_at = new \DateTimeImmutable($created_at);
        }
        return new ChatMessage(
            (int) $row['id'],
            (string) $row['text'],
            (string) $row['user_id'],
            (string) $row['room_id'],
            isset($row['reply_message_id']) && $row['reply_message_id'] !== null ? (int) $row['reply_message_id'] : null,
            $created_at,
        );
    }
}

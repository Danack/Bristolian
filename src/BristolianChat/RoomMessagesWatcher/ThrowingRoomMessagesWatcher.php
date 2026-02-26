<?php

declare(strict_types=1);

namespace BristolianChat\RoomMessagesWatcher;

use Bristolian\Model\Generated\ChatMessage;

/**
 * RoomMessagesWatcher that throws when getNextChatMessageAfter is called.
 * Used to test the exception-handling path in RoomMessageFetcher.
 */
class ThrowingRoomMessagesWatcher implements RoomMessagesWatcher
{
    public function __construct(
        private readonly int $initialPreviousId = 1,
        private readonly \Throwable $exception = new \RuntimeException('Database connection lost'),
    ) {
    }

    public function getInitialPreviousId(): int
    {
        return $this->initialPreviousId;
    }

    public function getNextChatMessageAfter(int $previousId): ChatMessage|null
    {
        throw $this->exception;
    }
}

<?php

declare(strict_types=1);

namespace BristolianChat\RoomMessagesWatcher;

/**
 * RoomMessageFetcher that throws when getNextChatMessageRowAfter is called.
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

    public function getNextChatMessageRowAfter(int $previousId): array|null
    {
        throw $this->exception;
    }
}

<?php

declare(strict_types=1);

namespace BristolianChat\RoomMessagesWatcher;

use Bristolian\Model\Generated\ChatMessage;

/**
 * Fetches chat messages for RoomMessageFetcher. In production the messages come from the
 * MySQL database, but fakes exist for testing purposes.
 */
interface RoomMessagesWatcher
{
    /**
     * Get the initial previous_id for watching: 0 when no messages exist, else the max message id.
     */
    public function getInitialPreviousId(): int;

    /**
     * Get the next chat message after the given id.
     */
    public function getNextChatMessageAfter(int $previousId): ChatMessage|null;
}

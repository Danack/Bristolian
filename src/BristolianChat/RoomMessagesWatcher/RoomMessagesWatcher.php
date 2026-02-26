<?php

declare(strict_types=1);

namespace BristolianChat\RoomMessagesWatcher;

/**
 * Fetches chat message rows for RoomMessageFetcher.
 * Abstraction over MySQL so the watcher can be tested with a Fake.
 */
interface RoomMessagesWatcher
{
    /**
     * Get the initial previous_id for watching: 0 when no messages exist, else the max message id.
     */
    public function getInitialPreviousId(): int;

    /**
     * Get the next chat message row after the given id.
     * Row must contain keys required by UserChatMessage::fromArray (id, reply_message_id, room_id, user_id, text, created_at).
     *
     * @return array<string, mixed>|null
     */
    public function getNextChatMessageRowAfter(int $previousId): array|null;
}

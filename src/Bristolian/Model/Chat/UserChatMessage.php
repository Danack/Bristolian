<?php

namespace Bristolian\Model\Chat;

use Bristolian\FromString;
use Bristolian\ToString;

/**
 * A user chat message as pulled from the database.
 */
class UserChatMessage
{
    use ToString;
    use FromString;

    public function __construct(
        public readonly int $id,
        public readonly string $user_id,
        public readonly string $room_id,
        public readonly string $text,
        public readonly int|null $reply_message_id,
        public readonly \DateTimeInterface $created_at,
    ) {
    }

    public function withText(string $text): self
    {
        return new self(
            $this->id,
            $this->user_id,
            $this->room_id,
            $text,
            $this->reply_message_id,
            $this->created_at
        );
    }
}
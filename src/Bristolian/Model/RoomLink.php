<?php

namespace Bristolian\Model;

use Bristolian\ToArray;

class RoomLink
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $link_id,
        public readonly string $url,
        public readonly string|null $title,
        public readonly string|null $description,
        public readonly string $room_id,
        public readonly string $user_id,
        // TODO - this should be DateTimeInterface ?
        public readonly string $created_at
    ) {
    }
}

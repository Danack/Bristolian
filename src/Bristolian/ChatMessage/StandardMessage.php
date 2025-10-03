<?php

namespace Bristolian\ChatMessage;

use Bristolian\Keys\RoomMessageKey;
use Bristolian\ToString;

class StandardMessage
{
    use ToString;

    public function __construct(
        //        public readonly string $room_id,
        //        public readonly string $user_id,
        //        public readonly string $user_name,
        public readonly string $message
    ) {
    }
}

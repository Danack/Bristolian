<?php

namespace Bristolian\Events;

use Bristolian\Keys\ContentModifiedKey;
use Bristolian\ToString;

class ContentAdded
{
    use ToString;

    public function __construct(
        public readonly string $room_id,
        public readonly string $room_name,
        public readonly string $user_id,
        public readonly string $user_name,
        public readonly string $description
    ) {
    }
}
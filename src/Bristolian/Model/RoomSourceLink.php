<?php

namespace Bristolian\Model;

use Bristolian\ToArray;

/**
 * An annotation in a PDF. TODO - change the name to annotations and add user-draw lines.
 */
class RoomSourceLink
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $file_id,
        public readonly string $highlights_json,
        public readonly string $text,
        public readonly string $title,
        public readonly string $room_sourcelink_id
    ) {
    }
}

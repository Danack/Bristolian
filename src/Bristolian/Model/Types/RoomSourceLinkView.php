<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;

/**
 * A denormalized view combining sourcelink and room_sourcelink tables.
 * Represents a source link annotation in a PDF with its room-specific metadata.
 */
class RoomSourceLinkView
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $file_id,
        public readonly string $highlights_json,
        public readonly string $text,
        public readonly ?string $title,
        public readonly string $room_sourcelink_id
    ) {
    }
}

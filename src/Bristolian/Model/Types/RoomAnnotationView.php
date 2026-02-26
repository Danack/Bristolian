<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;

/**
 * A denormalized view combining annotation and room_annotation tables.
 * Represents an annotation in a PDF with its room-specific metadata.
 */
class RoomAnnotationView
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $file_id,
        public readonly string $highlights_json,
        public readonly string $text,
        public readonly ?string $title,
        public readonly string $room_annotation_id
    ) {
    }
}

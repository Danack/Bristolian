<?php

declare(strict_types = 1);

namespace Bristolian\Model\Types;

use Bristolian\Model\Generated\RoomTag;
use Bristolian\ToArray;

/**
 * Room annotation view with tags for list API responses.
 */
class RoomAnnotationWithTags
{
    use ToArray;

    /**
     * @param RoomTag[] $tags
     */
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $file_id,
        public readonly string $highlights_json,
        public readonly string $text,
        public readonly ?string $title,
        public readonly string $room_annotation_id,
        public readonly array $tags
    ) {
    }
}

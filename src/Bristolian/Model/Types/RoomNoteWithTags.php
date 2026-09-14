<?php

declare(strict_types=1);

namespace Bristolian\Model\Types;

use BristolianGenerated\Model\RoomTag;
use Bristolian\ToArray;

/**
 * Room note with tags for list API responses.
 */
class RoomNoteWithTags
{
    use ToArray;

    /**
     * @param RoomTag[] $tags
     */
    public function __construct(
        public readonly string $id,
        public readonly string $room_id,
        public readonly string $user_id,
        public readonly string $title,
        public readonly string $markdown,
        public readonly \DateTimeInterface $created_at,
        public readonly \DateTimeInterface $updated_at,
        public readonly \DateTimeInterface|null $document_timestamp,
        public readonly array $tags
    ) {
    }
}

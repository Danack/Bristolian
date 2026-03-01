<?php

declare(strict_types = 1);

namespace Bristolian\Model\Types;

use Bristolian\Model\Generated\RoomTag;
use Bristolian\ToArray;

/**
 * Room link with tags for list API responses.
 */
class RoomLinkWithTags
{
    use ToArray;

    /**
     * @param RoomTag[] $tags
     */
    public function __construct(
        public readonly string $id,
        public readonly string $room_id,
        public readonly string $link_id,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly \DateTimeInterface $created_at,
        public readonly array $tags
    ) {
    }
}

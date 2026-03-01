<?php

declare(strict_types = 1);

namespace Bristolian\Model\Types;

use Bristolian\Model\Generated\RoomTag;
use Bristolian\ToArray;

/**
 * Room file (RoomFileObjectInfo) with tags for list API responses.
 */
class RoomFileWithTags
{
    use ToArray;

    /**
     * @param RoomTag[] $tags
     */
    public function __construct(
        public readonly string $id,
        public readonly string $normalized_name,
        public readonly string $original_filename,
        public readonly string $state,
        public readonly int $size,
        public readonly string $user_id,
        public readonly \DateTimeInterface $created_at,
        public readonly array $tags
    ) {
    }
}

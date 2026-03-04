<?php

declare(strict_types = 1);

namespace Bristolian\Model\Types;

use Bristolian\Model\Generated\RoomTag;
use Bristolian\ToArray;

/**
 * Room video with tags (for list API responses). Clips have parent_room_video_id set; front end may fetch parent details if needed.
 */
class RoomVideoWithTags
{
    use ToArray;

    /**
     * @param RoomTag[] $tags
     */
    public function __construct(
        public readonly string $id,
        public readonly string $room_id,
        public readonly string $video_id,
        public readonly string $youtube_video_id,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly ?string $parent_room_video_id,
        public readonly ?int $start_seconds,
        public readonly ?int $end_seconds,
        public readonly \DateTimeInterface $created_at,
        public readonly array $tags
    ) {
    }
}

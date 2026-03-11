<?php

declare(strict_types = 1);

namespace Bristolian\Model\Types;

/**
 * File in a room with room-level fields (e.g. document_timestamp from room_file).
 * Used for list responses; getFileDetails continues to return RoomFileObjectInfo.
 */
class RoomFileInRoom
{
    public function __construct(
        public readonly string $id,
        public readonly string $normalized_name,
        public readonly string $original_filename,
        public readonly string $state,
        public readonly int $size,
        public readonly string $user_id,
        public readonly \DateTimeInterface $created_at,
        public readonly ?\DateTimeInterface $document_timestamp
    ) {
    }
}

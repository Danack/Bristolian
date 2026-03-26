<?php

declare(strict_types = 1);

namespace Bristolian\Model\Types;

/**
 * Room link row including URL from the link table.
 */
class RoomLinkWithUrl
{
    public function __construct(
        public readonly string $id,
        public readonly string $room_id,
        public readonly string $link_id,
        public readonly string $url,
        public readonly ?string $title,
        public readonly ?string $description,
        public readonly \DateTimeInterface $created_at,
        public readonly ?\DateTimeInterface $document_timestamp
    ) {
    }
}

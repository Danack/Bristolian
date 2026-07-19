<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomLinkTagRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_link_tag;

interface RoomLinkTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room link
     */
    #[ReadsTable(room_link_tag::class)]
    public function getTagIdsForRoomLink(string $room_link_id): array;

    /**
     * @param array<string> $tag_ids
     */
    #[WritesTable(room_link_tag::class)]
    public function setTagsForRoomLink(string $room_link_id, array $tag_ids): void;
}

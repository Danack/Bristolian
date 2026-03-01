<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomLinkTagRepo;

interface RoomLinkTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room link
     */
    public function getTagIdsForRoomLink(string $room_link_id): array;

    public function setTagsForRoomLink(string $room_link_id, array $tag_ids): void;
}

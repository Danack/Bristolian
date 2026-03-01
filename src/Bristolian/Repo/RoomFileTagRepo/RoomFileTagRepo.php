<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomFileTagRepo;

interface RoomFileTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room file
     */
    public function getTagIdsForRoomFile(string $room_id, string $stored_file_id): array;

    public function setTagsForRoomFile(string $room_id, string $stored_file_id, array $tag_ids): void;
}

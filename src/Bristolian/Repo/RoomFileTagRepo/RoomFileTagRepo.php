<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomFileTagRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_file_tag;

interface RoomFileTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room file
     */
    #[ReadsTable(room_file_tag::class)]
    public function getTagIdsForRoomFile(string $room_id, string $stored_file_id): array;

    /**
     * @param array<string> $tag_ids
     */
    #[WritesTable(room_file_tag::class)]
    public function setTagsForRoomFile(string $room_id, string $stored_file_id, array $tag_ids): void;
}

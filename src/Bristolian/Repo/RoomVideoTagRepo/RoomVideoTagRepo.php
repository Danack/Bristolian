<?php

namespace Bristolian\Repo\RoomVideoTagRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_video_tag;

interface RoomVideoTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room video
     */
    #[ReadsTable(room_video_tag::class)]
    public function getTagIdsForRoomVideo(string $room_video_id): array;

    /**
     * @param array<string> $tag_ids
     */
    #[WritesTable(room_video_tag::class)]
    public function setTagsForRoomVideo(string $room_video_id, array $tag_ids): void;
}

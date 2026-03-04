<?php

namespace Bristolian\Repo\RoomVideoTagRepo;

interface RoomVideoTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room video
     */
    public function getTagIdsForRoomVideo(string $room_video_id): array;

    /**
     * @param array<string> $tag_ids
     */
    public function setTagsForRoomVideo(string $room_video_id, array $tag_ids): void;
}

<?php

namespace Bristolian\Repo\RoomVideoTagRepo;

class InMemoryRoomVideoTagRepo implements RoomVideoTagRepo
{
    /** @var array<string, string[]> keyed by room_video_id */
    private array $tagsByRoomVideo = [];

    /**
     * @return string[]
     */
    public function getTagIdsForRoomVideo(string $room_video_id): array
    {
        return $this->tagsByRoomVideo[$room_video_id] ?? [];
    }

    /**
     * @param array<string> $tag_ids
     */
    public function setTagsForRoomVideo(string $room_video_id, array $tag_ids): void
    {
        $this->tagsByRoomVideo[$room_video_id] = array_values($tag_ids);
    }
}

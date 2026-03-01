<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomLinkTagRepo;

/**
 * In-memory: key is room_link_id, value is string[] of tag_ids.
 */
class FakeRoomLinkTagRepo implements RoomLinkTagRepo
{
    /**
     * @var array<string, string[]>
     */
    private array $storage = [];

    /**
     * @return string[]
     */
    public function getTagIdsForRoomLink(string $room_link_id): array
    {
        return $this->storage[$room_link_id] ?? [];
    }

    public function setTagsForRoomLink(string $room_link_id, array $tag_ids): void
    {
        $this->storage[$room_link_id] = array_values($tag_ids);
    }
}

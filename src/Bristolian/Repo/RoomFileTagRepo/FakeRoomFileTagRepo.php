<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomFileTagRepo;

/**
 * In-memory storage: key is "room_id|stored_file_id", value is string[] of tag_ids.
 */
class FakeRoomFileTagRepo implements RoomFileTagRepo
{
    /**
     * @var array<string, string[]>
     */
    private array $storage = [];

    private function key(string $room_id, string $stored_file_id): string
    {
        return $room_id . '|' . $stored_file_id;
    }

    /**
     * @return string[]
     */
    public function getTagIdsForRoomFile(string $room_id, string $stored_file_id): array
    {
        return $this->storage[$this->key($room_id, $stored_file_id)] ?? [];
    }

    public function setTagsForRoomFile(string $room_id, string $stored_file_id, array $tag_ids): void
    {
        $this->storage[$this->key($room_id, $stored_file_id)] = array_values($tag_ids);
    }
}

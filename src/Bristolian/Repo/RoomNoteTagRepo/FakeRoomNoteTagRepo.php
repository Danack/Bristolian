<?php

declare(strict_types=1);

namespace Bristolian\Repo\RoomNoteTagRepo;

class FakeRoomNoteTagRepo implements RoomNoteTagRepo
{
    /**
     * @var array<string, string[]>
     */
    private array $storage = [];

    /**
     * @return string[]
     */
    public function getTagIdsForRoomNote(string $room_note_id): array
    {
        return $this->storage[$room_note_id] ?? [];
    }

    /**
     * @param string[] $tag_ids
     */
    public function setTagsForRoomNote(string $room_note_id, array $tag_ids): void
    {
        $this->storage[$room_note_id] = array_values($tag_ids);
    }
}

<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomAnnotationTagRepo;

/**
 * In-memory: key is room_annotation_id, value is string[] of tag_ids.
 */
class FakeRoomAnnotationTagRepo implements RoomAnnotationTagRepo
{
    /**
     * @var array<string, string[]>
     */
    private array $storage = [];

    /**
     * @return string[]
     */
    public function getTagIdsForRoomAnnotation(string $room_annotation_id): array
    {
        return $this->storage[$room_annotation_id] ?? [];
    }

    /**
     * @param array<string> $tag_ids
     */
    public function setTagsForRoomAnnotation(string $room_annotation_id, array $tag_ids): void
    {
        $this->storage[$room_annotation_id] = array_values($tag_ids);
    }
}

<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomAnnotationTagRepo;

interface RoomAnnotationTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room annotation
     */
    public function getTagIdsForRoomAnnotation(string $room_annotation_id): array;

    public function setTagsForRoomAnnotation(string $room_annotation_id, array $tag_ids): void;
}

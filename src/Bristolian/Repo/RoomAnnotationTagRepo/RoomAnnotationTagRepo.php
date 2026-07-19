<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomAnnotationTagRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_annotation_tag;

interface RoomAnnotationTagRepo
{
    /**
     * @return string[] tag_ids assigned to this room annotation
     */
    #[ReadsTable(room_annotation_tag::class)]
    public function getTagIdsForRoomAnnotation(string $room_annotation_id): array;

    /**
     * @param array<string> $tag_ids
     */
    #[WritesTable(room_annotation_tag::class)]
    public function setTagsForRoomAnnotation(string $room_annotation_id, array $tag_ids): void;
}

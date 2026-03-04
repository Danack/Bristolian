<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomAnnotationTagRepo;

use Bristolian\PdoSimple\PdoSimple;

class PdoRoomAnnotationTagRepo implements RoomAnnotationTagRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return string[]
     */
    public function getTagIdsForRoomAnnotation(string $room_annotation_id): array
    {
        $sql = "SELECT tag_id FROM room_annotation_tag WHERE room_annotation_id = :room_annotation_id";
        $rows = $this->pdoSimple->fetchAllAsData($sql, [':room_annotation_id' => $room_annotation_id]);
        return array_map(fn ($row) => $row['tag_id'], $rows);
    }

    /**
     * @param array<string> $tag_ids
     */
    public function setTagsForRoomAnnotation(string $room_annotation_id, array $tag_ids): void
    {
        $this->pdoSimple->execute(
            'DELETE FROM room_annotation_tag WHERE room_annotation_id = :room_annotation_id',
            [':room_annotation_id' => $room_annotation_id]
        );
        foreach ($tag_ids as $tag_id) {
            $this->pdoSimple->insert(
                'INSERT INTO room_annotation_tag (room_annotation_id, tag_id) VALUES (:room_annotation_id, :tag_id)',
                [':room_annotation_id' => $room_annotation_id, ':tag_id' => $tag_id]
            );
        }
    }
}

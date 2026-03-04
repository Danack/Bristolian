<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomFileTagRepo;

use Bristolian\PdoSimple\PdoSimple;

class PdoRoomFileTagRepo implements RoomFileTagRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return string[]
     */
    public function getTagIdsForRoomFile(string $room_id, string $stored_file_id): array
    {
        $sql = "SELECT tag_id FROM room_file_tag WHERE room_id = :room_id AND stored_file_id = :stored_file_id";
        $rows = $this->pdoSimple->fetchAllAsData($sql, [
            ':room_id' => $room_id,
            ':stored_file_id' => $stored_file_id,
        ]);
        return array_map(fn ($row) => $row['tag_id'], $rows);
    }

    /**
     * @param array<string> $tag_ids
     */
    public function setTagsForRoomFile(string $room_id, string $stored_file_id, array $tag_ids): void
    {
        $this->pdoSimple->execute(
            'DELETE FROM room_file_tag WHERE room_id = :room_id AND stored_file_id = :stored_file_id',
            [':room_id' => $room_id, ':stored_file_id' => $stored_file_id]
        );
        foreach ($tag_ids as $tag_id) {
            $this->pdoSimple->insert(
                'INSERT INTO room_file_tag (room_id, stored_file_id, tag_id) VALUES (:room_id, :stored_file_id, :tag_id)',
                [':room_id' => $room_id, ':stored_file_id' => $stored_file_id, ':tag_id' => $tag_id]
            );
        }
    }
}

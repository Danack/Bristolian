<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomLinkTagRepo;

use Bristolian\PdoSimple\PdoSimple;

class PdoRoomLinkTagRepo implements RoomLinkTagRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return string[]
     */
    public function getTagIdsForRoomLink(string $room_link_id): array
    {
        $sql = "SELECT tag_id FROM room_link_tag WHERE room_link_id = :room_link_id";
        $rows = $this->pdoSimple->fetchAllAsData($sql, [':room_link_id' => $room_link_id]);
        return array_map(fn ($row) => $row['tag_id'], $rows);
    }

    public function setTagsForRoomLink(string $room_link_id, array $tag_ids): void
    {
        $this->pdoSimple->execute(
            'DELETE FROM room_link_tag WHERE room_link_id = :room_link_id',
            [':room_link_id' => $room_link_id]
        );
        foreach ($tag_ids as $tag_id) {
            $this->pdoSimple->insert(
                'INSERT INTO room_link_tag (room_link_id, tag_id) VALUES (:room_link_id, :tag_id)',
                [':room_link_id' => $room_link_id, ':tag_id' => $tag_id]
            );
        }
    }
}

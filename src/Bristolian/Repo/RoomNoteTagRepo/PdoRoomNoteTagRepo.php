<?php

declare(strict_types=1);

namespace Bristolian\Repo\RoomNoteTagRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_note_tag;
use Bristolian\PdoSimple\PdoSimple;

class PdoRoomNoteTagRepo implements RoomNoteTagRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return string[]
     */
    #[ReadsTable(room_note_tag::class)]
    public function getTagIdsForRoomNote(string $room_note_id): array
    {
        $sql = "SELECT tag_id FROM room_note_tag WHERE room_note_id = :room_note_id";
        $rows = $this->pdoSimple->fetchAllAsData($sql, [':room_note_id' => $room_note_id]);
        return array_map(fn ($row) => $row['tag_id'], $rows);
    }

    /**
     * @param string[] $tag_ids
     */
    #[WritesTable(room_note_tag::class)]
    public function setTagsForRoomNote(string $room_note_id, array $tag_ids): void
    {
        $this->pdoSimple->execute(
            'DELETE FROM room_note_tag WHERE room_note_id = :room_note_id',
            [':room_note_id' => $room_note_id]
        );
        foreach ($tag_ids as $tag_id) {
            $this->pdoSimple->insert(
                'INSERT INTO room_note_tag (room_note_id, tag_id) VALUES (:room_note_id, :tag_id)',
                [':room_note_id' => $room_note_id, ':tag_id' => $tag_id]
            );
        }
    }
}

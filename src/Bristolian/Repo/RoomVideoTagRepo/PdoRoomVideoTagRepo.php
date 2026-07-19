<?php

namespace Bristolian\Repo\RoomVideoTagRepo;

use Bristolian\Database\room_video_tag;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;

class PdoRoomVideoTagRepo implements RoomVideoTagRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return string[]
     */
    #[ReadsTable(room_video_tag::class)]
    public function getTagIdsForRoomVideo(string $room_video_id): array
    {
        $sql = room_video_tag::SELECT . " where room_video_id = :room_video_id";
        $rows = $this->pdoSimple->fetchAllAsData($sql, ['room_video_id' => $room_video_id]);
        return array_map(fn ($row) => $row['tag_id'], $rows);
    }

    // TODO - change to add and remove, not replace
    /**
     * @param array<string> $tag_ids
     */
    #[WritesTable(room_video_tag::class)]
    public function setTagsForRoomVideo(string $room_video_id, array $tag_ids): void
    {
        $this->pdoSimple->execute(
            'DELETE FROM room_video_tag WHERE room_video_id = :room_video_id',
            ['room_video_id' => $room_video_id]
        );
        foreach ($tag_ids as $tag_id) {
            $this->pdoSimple->insert(room_video_tag::INSERT, [
                'room_video_id' => $room_video_id,
                'tag_id' => $tag_id,
            ]);
        }
    }
}

<?php

declare(strict_types=1);

namespace Bristolian\Repo\RoomNoteTagRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\room_note_tag;

interface RoomNoteTagRepo
{
    /**
     * @return string[]
     */
    #[ReadsTable(room_note_tag::class)]
    public function getTagIdsForRoomNote(string $room_note_id): array;

    /**
     * @param string[] $tag_ids
     */
    #[WritesTable(room_note_tag::class)]
    public function setTagsForRoomNote(string $room_note_id, array $tag_ids): void;
}

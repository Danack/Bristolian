<?php

declare(strict_types=1);

namespace Bristolian\Repo\RoomNoteRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\room_note;
use BristolianGenerated\Database\room_note_tag;
use Bristolian\Exception\ContentNotFoundException;
use BristolianGenerated\Model\RoomNote;
use Bristolian\Parameters\RoomContentSearchParams;

interface RoomNoteRepo
{
    /**
     * @return RoomNote[]
     */
    #[ReadsTable(room_note::class)]
    #[ReadsTable(room_note_tag::class)]
    public function getNotesForRoom(string $room_id, RoomContentSearchParams $search): array;

    /**
     * @throws ContentNotFoundException
     */
    #[ReadsTable(room_note::class)]
    public function getNote(string $room_id, string $room_note_id): RoomNote;

    #[WritesTable(room_note::class)]
    public function create(
        string $user_id,
        string $room_id,
        string $title,
        string $markdown,
        \DateTimeInterface|null $document_timestamp
    ): string;

    /**
     * @throws ContentNotFoundException
     */
    #[WritesTable(room_note::class)]
    public function update(
        string $room_id,
        string $room_note_id,
        string $title,
        string $markdown,
        \DateTimeInterface|null $document_timestamp
    ): void;

    /**
     * @throws ContentNotFoundException
     */
    #[WritesTable(room_note::class)]
    public function delete(string $room_id, string $room_note_id): void;
}

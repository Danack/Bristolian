<?php

declare(strict_types=1);

namespace Bristolian\Repo\RoomNoteRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomNote;
use Bristolian\Parameters\RoomContentSearchParams;
use Ramsey\Uuid\Uuid;
use Safe\DateTimeImmutable;

class FakeRoomNoteRepo implements RoomNoteRepo
{
    /**
     * @var RoomNote[]
     */
    private array $notes = [];

    /**
     * @return RoomNote[]
     */
    public function getNotesForRoom(string $room_id, RoomContentSearchParams $search): array
    {
        $notes = [];
        foreach ($this->notes as $note) {
            if ($note->room_id === $room_id) {
                $notes[] = $note;
            }
        }

        $notes = array_values(array_filter($notes, function (RoomNote $note) use ($search): bool {
            if ($search->title !== null && $search->title !== '' && stripos($note->title, $search->title) === false) {
                return false;
            }
            if ($search->description !== null && $search->description !== '' && stripos($note->markdown, $search->description) === false) {
                return false;
            }
            if ($search->created_at_after !== null && $note->created_at < $search->created_at_after) {
                return false;
            }
            if ($search->created_at_before !== null && $note->created_at > $search->created_at_before) {
                return false;
            }
            if ($search->document_timestamp_after !== null && $note->document_timestamp !== null && $note->document_timestamp < $search->document_timestamp_after) {
                return false;
            }
            if ($search->document_timestamp_before !== null && $note->document_timestamp !== null && $note->document_timestamp > $search->document_timestamp_before) {
                return false;
            }

            return true;
        }));

        usort($notes, fn (RoomNote $a, RoomNote $b) => $b->created_at <=> $a->created_at);

        return array_slice($notes, 0, $search->getLimit());
    }

    public function getNote(string $room_id, string $room_note_id): RoomNote
    {
        foreach ($this->notes as $note) {
            if ($note->id === $room_note_id && $note->room_id === $room_id) {
                return $note;
            }
        }

        throw ContentNotFoundException::room_note_not_found($room_id, $room_note_id);
    }

    public function create(
        string $user_id,
        string $room_id,
        string $title,
        string $markdown,
        \DateTimeInterface|null $document_timestamp
    ): string {
        $id = Uuid::uuid7()->toString();
        $now = new DateTimeImmutable('2010-01-28T15:00:00+02:00');
        $this->notes[] = new RoomNote(
            $id,
            $room_id,
            $user_id,
            $title,
            $markdown,
            $document_timestamp,
            $now,
            $now
        );

        return $id;
    }

    public function update(
        string $room_id,
        string $room_note_id,
        string $title,
        string $markdown,
        \DateTimeInterface|null $document_timestamp
    ): void {
        foreach ($this->notes as $index => $note) {
            if ($note->id !== $room_note_id || $note->room_id !== $room_id) {
                continue;
            }

            $this->notes[$index] = new RoomNote(
                $note->id,
                $note->room_id,
                $note->user_id,
                $title,
                $markdown,
                $document_timestamp,
                $note->created_at,
                new DateTimeImmutable('2011-02-01T12:00:00+00:00')
            );
            return;
        }

        throw ContentNotFoundException::room_note_not_found($room_id, $room_note_id);
    }

    public function delete(string $room_id, string $room_note_id): void
    {
        foreach ($this->notes as $index => $note) {
            if ($note->id === $room_note_id && $note->room_id === $room_id) {
                unset($this->notes[$index]);
                $this->notes = array_values($this->notes);
                return;
            }
        }

        throw ContentNotFoundException::room_note_not_found($room_id, $room_note_id);
    }
}

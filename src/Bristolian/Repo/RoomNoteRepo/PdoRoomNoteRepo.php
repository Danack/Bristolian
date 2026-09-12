<?php

declare(strict_types=1);

namespace Bristolian\Repo\RoomNoteRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room_note;
use Bristolian\Database\room_note_tag;
use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Model\Generated\RoomNote;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomNoteRepo implements RoomNoteRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @return RoomNote[]
     */
    #[ReadsTable(room_note::class)]
    #[ReadsTable(room_note_tag::class)]
    public function getNotesForRoom(string $room_id, RoomContentSearchParams $search): array
    {
        $where = ['room_id = :room_id'];
        $params = [
            'room_id' => $room_id,
            'limit' => $search->getLimit(),
        ];

        if ($search->title !== null && $search->title !== '') {
            $where[] = 'title LIKE :title_pattern';
            $params['title_pattern'] = '%' . str_replace(['%', '_'], ['\%', '\_'], $search->title) . '%';
        }
        if ($search->description !== null && $search->description !== '') {
            $where[] = 'markdown LIKE :description_pattern';
            $params['description_pattern'] = '%' . str_replace(['%', '_'], ['\%', '\_'], $search->description) . '%';
        }
        $createdAtAfter = $search->getCreatedAtAfterForSql();
        if ($createdAtAfter !== null) {
            $where[] = 'created_at >= :created_at_after';
            $params['created_at_after'] = $createdAtAfter;
        }
        $createdAtBefore = $search->getCreatedAtBeforeForSql();
        if ($createdAtBefore !== null) {
            $where[] = 'created_at <= :created_at_before';
            $params['created_at_before'] = $createdAtBefore;
        }
        $documentTimestampAfter = $search->getDocumentTimestampAfterForSql();
        if ($documentTimestampAfter !== null) {
            $where[] = 'document_timestamp >= :document_timestamp_after';
            $params['document_timestamp_after'] = $documentTimestampAfter;
        }
        $documentTimestampBefore = $search->getDocumentTimestampBeforeForSql();
        if ($documentTimestampBefore !== null) {
            $where[] = 'document_timestamp <= :document_timestamp_before';
            $params['document_timestamp_before'] = $documentTimestampBefore;
        }

        $tagIds = $search->getTagIds();
        if (count($tagIds) > 0) {
            $placeholders = [];
            foreach ($tagIds as $index => $tagId) {
                $key = ':tag_id_' . $index;
                $placeholders[] = $key;
                $params[$key] = $tagId;
            }
            $params[':tag_count'] = count($tagIds);
            $where[] = 'id IN (SELECT room_note_id FROM room_note_tag WHERE tag_id IN (' . implode(', ', $placeholders) . ') GROUP BY room_note_id HAVING COUNT(DISTINCT tag_id) = :tag_count)';
        }

        $whereClause = implode(' and ', $where);
        $sql = room_note::SELECT . " where {$whereClause} order by created_at desc limit :limit";

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomNote::class
        );
    }

    #[ReadsTable(room_note::class)]
    public function getNote(string $room_id, string $room_note_id): RoomNote
    {
        $sql = room_note::SELECT . " where id = :id and room_id = :room_id";
        $note = $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [
                'id' => $room_note_id,
                'room_id' => $room_id,
            ],
            RoomNote::class
        );
        if ($note === null) {
            throw ContentNotFoundException::room_note_not_found($room_id, $room_note_id);
        }

        return $note;
    }

    #[WritesTable(room_note::class)]
    public function create(
        string $user_id,
        string $room_id,
        string $title,
        string $markdown,
        \DateTimeInterface|null $document_timestamp
    ): string {
        $id = Uuid::uuid7()->toString();
        $this->pdoSimple->insert(room_note::INSERT, [
            'id' => $id,
            'room_id' => $room_id,
            'user_id' => $user_id,
            'title' => $title,
            'markdown' => $markdown,
            'document_timestamp' => $document_timestamp,
        ]);

        return $id;
    }

    #[WritesTable(room_note::class)]
    public function update(
        string $room_id,
        string $room_note_id,
        string $title,
        string $markdown,
        \DateTimeInterface|null $document_timestamp
    ): void {
        $sql = <<< SQL
update room_note
set
  title = :title,
  markdown = :markdown,
  document_timestamp = :document_timestamp
where
  id = :id
and
  room_id = :room_id
SQL;

        $rowsAffected = $this->pdoSimple->execute($sql, [
            'title' => $title,
            'markdown' => $markdown,
            'document_timestamp' => $document_timestamp?->format('Y-m-d H:i:s'),
            'id' => $room_note_id,
            'room_id' => $room_id,
        ]);

        if ($rowsAffected === 0) {
            throw ContentNotFoundException::room_note_not_found($room_id, $room_note_id);
        }
    }

    #[WritesTable(room_note::class)]
    public function delete(string $room_id, string $room_note_id): void
    {
        $sql = 'DELETE FROM room_note WHERE id = :id AND room_id = :room_id';
        $rowsAffected = $this->pdoSimple->execute($sql, [
            'id' => $room_note_id,
            'room_id' => $room_id,
        ]);

        if ($rowsAffected === 0) {
            throw ContentNotFoundException::room_note_not_found($room_id, $room_note_id);
        }
    }
}

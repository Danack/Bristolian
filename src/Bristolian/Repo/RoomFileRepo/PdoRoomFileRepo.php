<?php

namespace Bristolian\Repo\RoomFileRepo;

use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Model\Types\RoomFileInRoom;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\PdoSimple\PdoSimple;

class PdoRoomFileRepo implements RoomFileRepo
{
    public function __construct(
        private PdoSimple $pdoSimple
    ) {
    }

    // TODO - why are we passing IDs around and not objects?
    public function addFileToRoom(string $fileStorageId, string $room_id): void
    {
        $sql = <<< SQL
insert into room_file (
    room_id,
    stored_file_id
)
values (
    :room_id,
    :stored_file_id
)
SQL;

        $params = [
            ':room_id' => $room_id,
            ':stored_file_id' => $fileStorageId,
        ];

        $this->pdoSimple->insert($sql, $params);
    }

    /**
     * @param string $room_id
     * @return RoomFileInRoom[]
     * @throws \ReflectionException
     */
    public function getFilesForRoom(string $room_id, RoomContentSearchParams $search): array
    {
        $where = ['rf.room_id = :room_id'];
        $params = [
            ':room_id' => $room_id,
            ':limit' => $search->getLimit(),
        ];

        if ($search->title !== null && $search->title !== '') {
            $where[] = 'sf.original_filename LIKE :title_pattern';
            $params[':title_pattern'] = '%' . str_replace(['%', '_'], ['\%', '\_'], $search->title) . '%';
        }
        $createdAtAfter = $search->getCreatedAtAfterForSql();
        if ($createdAtAfter !== null) {
            $where[] = 'sf.created_at >= :created_at_after';
            $params[':created_at_after'] = $createdAtAfter;
        }
        $createdAtBefore = $search->getCreatedAtBeforeForSql();
        if ($createdAtBefore !== null) {
            $where[] = 'sf.created_at <= :created_at_before';
            $params[':created_at_before'] = $createdAtBefore;
        }
        $documentTimestampAfter = $search->getDocumentTimestampAfterForSql();
        if ($documentTimestampAfter !== null) {
            $where[] = 'rf.document_timestamp >= :document_timestamp_after';
            $params[':document_timestamp_after'] = $documentTimestampAfter;
        }
        $documentTimestampBefore = $search->getDocumentTimestampBeforeForSql();
        if ($documentTimestampBefore !== null) {
            $where[] = 'rf.document_timestamp <= :document_timestamp_before';
            $params[':document_timestamp_before'] = $documentTimestampBefore;
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
            $params[':room_id_tag_subquery'] = $room_id;
            $where[] = 'rf.stored_file_id IN (SELECT stored_file_id FROM room_file_tag WHERE room_id = :room_id_tag_subquery AND tag_id IN (' . implode(', ', $placeholders) . ') GROUP BY stored_file_id HAVING COUNT(DISTINCT tag_id) = :tag_count)';
        }

        $whereClause = implode(' and ', $where);
        $sql = <<< SQL
select
    sf.id,
    sf.normalized_name,
    sf.original_filename,
    sf.state,
    sf.size,
    sf.user_id,
    sf.created_at,
    rf.document_timestamp
from room_file_object_info as sf
left join room_file as rf on sf.id = rf.stored_file_id
where {$whereClause}
order by sf.created_at desc
limit :limit
SQL;

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomFileInRoom::class
        );
    }


    public function getFileDetails(string $room_id, string $file_id): RoomFileObjectInfo|null
    {
        $sql = <<< SQL
select  
    sf.id,
    sf.normalized_name,
    sf.original_filename,
    sf.state,
    sf.size,
    sf.user_id,
    sf.created_at
from
  room_file_object_info as sf
left join
   room_file as rf
on 
 sf.id = rf.stored_file_id
where
  room_id = :room_id and
  sf.id = :file_id

SQL;
        $params = [
            ':room_id' => $room_id,
            ':file_id' => $file_id
        ];

        return $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            RoomFileObjectInfo::class
        );
    }
}

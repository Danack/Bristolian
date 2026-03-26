<?php

namespace Bristolian\Repo\RoomLinkRepo;

use Bristolian\Database\room_link;
use Bristolian\Exception\BristolianException;
use Bristolian\Model\Generated\RoomLink;
use Bristolian\Model\Types\RoomLinkWithUrl;
use Bristolian\Parameters\LinkParam;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Repo\LinkRepo\LinkRepo;
use Ramsey\Uuid\Uuid;

class PdoRoomLinkRepo implements RoomLinkRepo
{
    public function __construct(
        private LinkRepo $linkRepo,
        private PdoSimple $pdoSimple
    ) {
    }

    public function addLinkToRoomFromParam(
        string $user_id,
        string $room_id,
        LinkParam $linkParam
    ): string {

        $link_id = $this->linkRepo->store_link($user_id, $linkParam->url);
        $sql = room_link::INSERT;

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            'id' => $id,
            'description' => $linkParam->description,
            'link_id' => $link_id,
            'room_id' => $room_id,
            'title' => $linkParam->title,
            'document_timestamp' => null,
        ];

        $this->pdoSimple->insert($sql, $params);

        return $id;
    }

    public function getRoomLink(string $room_link_id): RoomLink|null
    {
        $sql = room_link::SELECT;
        $sql .=  "where id = :id";

        $params = ['id' => $room_link_id];

        $result = $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            RoomLink::class
        );

        if ($result === null) {
            throw new BristolianException("Failed to find room link with 'id' => $room_link_id");
        }

        return $result;
    }

    /**
     * @param string $room_id
     * @return RoomLinkWithUrl[]
     * @throws \ReflectionException
     */
    public function getLinksForRoom(string $room_id, RoomContentSearchParams $search): array
    {
        $where = ['room_link.room_id = :room_id'];
        $params = [
            'room_id' => $room_id,
            'limit' => $search->getLimit(),
        ];

        if ($search->title !== null && $search->title !== '') {
            $where[] = 'room_link.title LIKE :title_pattern';
            $params['title_pattern'] = '%' . str_replace(['%', '_'], ['\%', '\_'], $search->title) . '%';
        }
        if ($search->description !== null && $search->description !== '') {
            $where[] = 'room_link.description LIKE :description_pattern';
            $params['description_pattern'] = '%' . str_replace(['%', '_'], ['\%', '\_'], $search->description) . '%';
        }
        $createdAtAfter = $search->getCreatedAtAfterForSql();
        if ($createdAtAfter !== null) {
            $where[] = 'room_link.created_at >= :created_at_after';
            $params['created_at_after'] = $createdAtAfter;
        }
        $createdAtBefore = $search->getCreatedAtBeforeForSql();
        if ($createdAtBefore !== null) {
            $where[] = 'room_link.created_at <= :created_at_before';
            $params['created_at_before'] = $createdAtBefore;
        }
        $documentTimestampAfter = $search->getDocumentTimestampAfterForSql();
        if ($documentTimestampAfter !== null) {
            $where[] = 'room_link.document_timestamp >= :document_timestamp_after';
            $params['document_timestamp_after'] = $documentTimestampAfter;
        }
        $documentTimestampBefore = $search->getDocumentTimestampBeforeForSql();
        if ($documentTimestampBefore !== null) {
            $where[] = 'room_link.document_timestamp <= :document_timestamp_before';
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
            $where[] = 'room_link.id IN (SELECT room_link_id FROM room_link_tag WHERE tag_id IN (' . implode(', ', $placeholders) . ') GROUP BY room_link_id HAVING COUNT(DISTINCT tag_id) = :tag_count)';
        }

        $whereClause = implode(' and ', $where);
        $sql = <<< SQL
select
    room_link.id,
    room_link.room_id,
    room_link.link_id,
    link.url,
    room_link.title,
    room_link.description,
    room_link.created_at,
    room_link.document_timestamp
from
  room_link
  join link on room_link.link_id = link.id
where {$whereClause}
order by room_link.created_at desc
limit :limit
SQL;

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomLinkWithUrl::class
        );
    }
}

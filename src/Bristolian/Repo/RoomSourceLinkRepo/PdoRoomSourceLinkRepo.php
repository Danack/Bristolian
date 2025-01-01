<?php

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\Database\room_sourcelink;
use Bristolian\Database\sourcelink;
use Bristolian\DataType\SourceLinkParam;
use Bristolian\Model\StoredFile;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;
use Bristolian\Model\RoomSourceLink;

class PdoRoomSourceLinkRepo implements RoomSourceLinkRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function addSourceLink(
        string $user_id,
        string $room_id,
        string $file_id,
        SourceLinkParam $sourceLinkParam
    ): string {
        $uuid = Uuid::uuid7();
        $sourcelink_id = $uuid->toString();
        $sql = sourcelink::INSERT;

        $params = [
            ':id' => $sourcelink_id,
            ':user_id' => $user_id,
            ':file_id' => $file_id,
            ':highlights_json' => $sourceLinkParam->highlights_json,
            ':text' => $sourceLinkParam->text
        ];
        $this->pdoSimple->execute($sql, $params);

        $sql2 = room_sourcelink::INSERT;
        $uuid = Uuid::uuid7();
        $room_sourcelink_id = $uuid->toString();
        $params2 = [
            ':id' => $room_sourcelink_id,
            ':room_id' => $room_id,
            ':sourcelink_id' => $sourcelink_id,
            ':title' => $sourceLinkParam->title
        ];

        $this->pdoSimple->execute($sql2, $params2);

        return $room_sourcelink_id;
    }

    /**
     * @param string $room_id
     * @return RoomSourceLink[]
     */
    public function getSourceLinksForRoom(string $room_id): array
    {
        $sql = <<< SQL
select  
    sl.id,
    sl.user_id,
    sl.file_id,
    sl.highlights_json,
    sl.text,
    rs.title,
    rs.id as room_sourcelink_id
from
  sourcelink sl
left join
  room_sourcelink rs
on 
 sl.id = rs.sourcelink_id
where
  room_id = :room_id
SQL;

        $params = [
            ':room_id' => $room_id
        ];

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomSourceLink::class
        );
    }


}

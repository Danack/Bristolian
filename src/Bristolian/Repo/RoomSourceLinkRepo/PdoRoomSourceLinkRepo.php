<?php

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\Database\room_sourcelink;
use Bristolian\Database\sourcelink;
use Bristolian\DataType\SourceLinkParam;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

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
}

<?php

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\Database\sourcelink;
use Bristolian\PdoSimple\PdoSimple;

class PdoRoomSourceLinkRepo implements RoomSourceLinkRepo
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    public function addSourceLink(
        string $user_id,
        string $room_id,
        string $file_id,
        string $title,
        string $highlights_json
    ): string {
        // TODO: Implement addSourceLink() method.

        $sql = \Bristolian\Database\sourcelink::INSERT;

        $params = [
        ':id',
        ':user_id',
        ':file_id',
        ':highlights_json'



        ];


        $this->pdoSimple->execute($sql, $params[]);
    }
}

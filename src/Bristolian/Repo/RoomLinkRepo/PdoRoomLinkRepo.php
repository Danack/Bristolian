<?php

namespace Bristolian\Repo\RoomLinkRepo;

use Bristolian\App;
use Bristolian\DataType\LinkParam;
use Bristolian\Model\AdminUser;
use Bristolian\Model\StoredFile;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;
use Bristolian\BristolianException;
use Bristolian\Database\room_link;
use Bristolian\Database\link as link;
use Bristolian\Model\RoomLink;
use Bristolian\Repo\LinkRepo\LinkRepo;

class PdoRoomLinkRepo implements RoomLinkRepo
{
    public function __construct(
        private LinkRepo $linkRepo,
        private PdoSimple $pdoSimple
    ) {
    }

    // TODO - why are we passing IDs around and not objects?
    public function addLinkToRoom(string $link_id, string $room_id)
    {
        $sql = room_link::SELECT;
        $params = [
            ':room_id' => $room_id,
            ':link_id' => $link_id,
        ];

        $this->pdoSimple->insert($sql, $params);
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
            'title' => $linkParam->title
        ];

        $this->pdoSimple->insert($sql, $params);

        return $id;
    }

    public function getRoomLink(string $room_link_id): RoomLink|null
    {
        $sql = <<< SQL
select
    rl.id,
    rl.link_id,
    l.url,
    rl.title,
    rl.description,
    rl.room_id,
    l.user_id,
    rl.created_at
from
  room_link rl
left join 
  link l
on
  l.id = rl.link_id
where
  rl.id = :id
SQL;

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
     * @return RoomLink[]
     * @throws \ReflectionException
     */
    public function getLinksForRoom(string $room_id): array
    {
        $sql = <<< SQL
select
    rl.id,
    l.user_id,
    l.url,
    rl.title,
    rl.link_id,
    rl.description,
    rl.room_id,
    rl.created_at
from
  link as l
left join
   room_link as rl
on 
 l.id = rl.link_id
where
  room_id = :room_id
SQL;
        $params = [
          ':room_id' => $room_id
        ];

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            RoomLink::class
        );
    }


//    public function getLinkDetails(string $room_id, string $link_id): RoomLink|null
//    {
//        $sql = <<< SQL
//select
//    sf.id,
//    sf.normalized_name,
//    sf.original_filename,
//    sf.state,
//    sf.size,
//    sf.user_id,
//    sf.created_at
//from
//  stored_file as sf
//left join
//   room_file as rf
//on
// sf.id = rf.stored_file_id
//where
//  room_id = :room_id and
//  sf.id = :file_id
//
//SQL;
//        $params = [
//            ':room_id' => $room_id,
//            ':link_id' => $link_id
//        ];
//
//        return $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
//            $sql,
//            $params,
//            RoomLink::class
//        );
//    }
}

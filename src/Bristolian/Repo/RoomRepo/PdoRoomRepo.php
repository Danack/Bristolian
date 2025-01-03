<?php

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Model\Room;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomRepo implements RoomRepo
{
    public function __construct(
        private PdoSimple $pdoSimple
    ) {
    }


    public function createRoom(string $owner_user_id, string $name, string $purpose): Room
    {
        $sql = <<< SQL
insert into room (
  id,
  owner_user_id,
  name,
  purpose
)
values (
  :id,
  :owner_user_id,
  :name,
  :purpose
)
SQL;
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            ':id' => $id,
             ':owner_user_id' => $owner_user_id,
             ':name' => $name,
             ':purpose' => $purpose
        ];

        $this->pdoSimple->insert($sql, $params);

        $room = $this->getRoomById($id);
        if ($room === null) {
            // @codeCoverageIgnoreStart
            throw new BristolianException("This should never happen.");
            // @codeCoverageIgnoreEnd
        }

        return $room;
    }


    public function getRoomById(string $room_id): Room|null
    {
        $sql = <<< SQL
select
  id,
  owner_user_id,
  name,
  purpose
from
  room
where
  id = :room_id
SQL;
        $params = [
          ':room_id' => $room_id
        ];

        return $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            Room::class
        );
    }

    /**
     * @return Room[]
     * @throws \Exception
     */
    public function getAllRooms(): array
    {
        $sql = <<< SQL
select
  id,
  owner_user_id,
  name,
  purpose
from
  room
SQL;

        return $this->pdoSimple->fetchAllAsObjectConstructor(
            $sql,
            [],
            Room::class
        );
    }
}

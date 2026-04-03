<?php

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Database\room as room_table;
use Bristolian\Model\Generated\Room;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

class PdoRoomRepo implements RoomRepo
{
    public function __construct(
        private PdoSimple $pdoSimple
    ) {
    }


    public function createRoom(string $user_id, string $name, string $purpose): Room
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
             ':owner_user_id' => $user_id,
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


    public function getRoomById(string $id): Room|null
    {
        $sql = <<< SQL
select
  id,
  owner_user_id,
  name,
  purpose,
  created_at
from
  room
where
  id = :room_id
SQL;
        $params = [
          ':room_id' => $id
        ];

        return $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            Room::class
        );
    }

    public function updateRoomNameAndPurpose(string $room_id, string $name, string $purpose): void
    {
        $this->pdoSimple->execute(room_table::UPDATE, [
            ':id' => $room_id,
            ':name' => $name,
            ':purpose' => $purpose,
        ]);
    }

    /**
     * @return Room[]
     * @throws \Exception
     */
    public function getRoomByName(string $name): array
    {
        $sql = <<< SQL
select
    id,
    owner_user_id,
    name,
    purpose,
    created_at
from
    room
where
    name = :name
SQL;
        $params = [
            ':name' => $name,
        ];

        return $this->pdoSimple->fetchAllAsObjectConstructor(
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
  purpose,
  created_at
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

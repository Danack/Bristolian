<?php

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Database\room as room_table;
use Bristolian\Model\Generated\Room;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;
use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;

class PdoRoomRepo implements RoomRepo
{
    public function __construct(
        private PdoSimple $pdoSimple
    ) {
    }


    #[WritesTable(room_table::class)]
    public function createRoom(string $user_id, string $name, string $purpose): Room
    {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            ':id' => $id,
            ':owner_user_id' => $user_id,
            ':name' => $name,
            ':purpose' => $purpose
        ];

        $this->pdoSimple->insert(room_table::INSERT, $params);

        $room = $this->getRoomById($id);
        if ($room === null) {
            // @codeCoverageIgnoreStart
            throw new BristolianException("This should never happen.");
            // @codeCoverageIgnoreEnd
        }

        return $room;
    }


    #[ReadsTable(room_table::class)]
    public function getRoomById(string $id): Room|null
    {
        $sql = room_table::SELECT . " where id = :room_id";
        $params = [
          ':room_id' => $id
        ];

        return $this->pdoSimple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            Room::class
        );
    }

    #[WritesTable(room_table::class)]
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
    #[ReadsTable(room_table::class)]
    public function getRoomByName(string $name): array
    {
        $sql = room_table::SELECT . " where name = :name";
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
    #[ReadsTable(room_table::class)]
    public function getAllRooms(): array
    {
        return $this->pdoSimple->fetchAllAsObjectConstructor(
            room_table::SELECT,
            [],
            Room::class
        );
    }
}

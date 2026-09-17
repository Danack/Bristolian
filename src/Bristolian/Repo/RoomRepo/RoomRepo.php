<?php

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\room as room_table;
use BristolianGenerated\Model\Room;

interface RoomRepo
{
    #[ReadsTable(room_table::class)]
    #[WritesTable(room_table::class)]
    public function createRoom(string $user_id, string $name, string $purpose): Room;

    #[ReadsTable(room_table::class)]
    public function getRoomById(string $id): Room|null;

    #[WritesTable(room_table::class)]
    public function updateRoomNameAndPurpose(string $room_id, string $name, string $purpose): void;

    /**
     * @return Room[] All rooms with this exact name (may be empty).
     */
    #[ReadsTable(room_table::class)]
    public function getRoomByName(string $name): array;

    public const DEFAULT_LIST_LIMIT = 100;

    /**
     * @return Room[] Up to $limit rooms, oldest id first.
     */
    #[ReadsTable(room_table::class)]
    public function getAllRooms(int $limit = self::DEFAULT_LIST_LIMIT): array;

    /**
     * @return Room[] Up to $limit rooms, newest id first (uuid7 ids are time-ordered).
     */
    #[ReadsTable(room_table::class)]
    public function getLatestRoomsCreated(int $limit = self::DEFAULT_LIST_LIMIT): array;
}

<?php

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\room as room_table;
use Bristolian\Model\Generated\Room;

interface RoomRepo
{
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

    /**
     * @return Room[]
     */
    #[ReadsTable(room_table::class)]
    public function getAllRooms(): array;
}

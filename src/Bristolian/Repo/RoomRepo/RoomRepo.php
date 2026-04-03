<?php

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Model\Generated\Room;

interface RoomRepo
{
    public function createRoom(string $user_id, string $name, string $purpose): Room;

    public function getRoomById(string $id): Room|null;

    public function updateRoomNameAndPurpose(string $room_id, string $name, string $purpose): void;

    /**
     * @return Room[] All rooms with this exact name (may be empty).
     */
    public function getRoomByName(string $name): array;

    /**
     * @return Room[]
     */
    public function getAllRooms(): array;
}

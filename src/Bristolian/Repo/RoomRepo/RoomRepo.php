<?php

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Model\Generated\Room;

interface RoomRepo
{
    public function createRoom(string $user_id, string $name, string $purpose): Room;

    public function getRoomById(string $id): Room|null;

    /**
     * @return Room[]
     */
    public function getAllRooms(): array;
}

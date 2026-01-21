<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomRepo;

use Bristolian\Model\Generated\Room;
use Ramsey\Uuid\Uuid;

/**
 * Fake implementation of RoomRepo for testing.
 */
class FakeRoomRepo implements RoomRepo
{
    /**
     * @var Room[]
     */
    private array $rooms = [];

    public function createRoom(string $user_id, string $name, string $purpose): Room
    {
        $uuid = Uuid::uuid7();
        $id = $uuid->toString();
        $now = new \DateTimeImmutable();

        $room = new Room(
            id: $id,
            owner_user_id: $user_id,
            name: $name,
            purpose: $purpose,
            created_at: $now
        );

        $this->rooms[$id] = $room;

        return $room;
    }

    public function getRoomById(string $id): Room|null
    {
        return $this->rooms[$id] ?? null;
    }

    /**
     * @return Room[]
     */
    public function getAllRooms(): array
    {
        return array_values($this->rooms);
    }
}
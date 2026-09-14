<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomRepo;

use BristolianGenerated\Model\Room;
use Ramsey\Uuid\Uuid;
use Safe\DateTimeImmutable;

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
        $now = new DateTimeImmutable();

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

    public function updateRoomNameAndPurpose(string $room_id, string $name, string $purpose): void
    {
        $existing = $this->rooms[$room_id] ?? null;
        if ($existing === null) {
            return;
        }
        $this->rooms[$room_id] = new Room(
            id: $existing->id,
            owner_user_id: $existing->owner_user_id,
            name: $name,
            purpose: $purpose,
            created_at: $existing->created_at
        );
    }

    /**
     * @return Room[]
     */
    public function getRoomByName(string $name): array
    {
        $matching = [];
        foreach ($this->rooms as $room) {
            if ($room->name === $name) {
                $matching[] = $room;
            }
        }

        return $matching;
    }

    /**
     * @return Room[]
     */
    public function getAllRooms(int $limit = RoomRepo::DEFAULT_LIST_LIMIT): array
    {
        $rooms = array_values($this->rooms);
        usort($rooms, fn (Room $left, Room $right) => strcmp($left->id, $right->id));

        return array_slice($rooms, 0, $limit);
    }

    /**
     * @return Room[]
     */
    public function getLatestRoomsCreated(int $limit = RoomRepo::DEFAULT_LIST_LIMIT): array
    {
        $rooms = array_values($this->rooms);
        usort($rooms, fn (Room $left, Room $right) => strcmp($right->id, $left->id));

        return array_slice($rooms, 0, $limit);
    }
}

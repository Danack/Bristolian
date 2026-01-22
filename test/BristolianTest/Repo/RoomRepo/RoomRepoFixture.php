<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use Bristolian\Model\Generated\Room;
use Bristolian\Repo\RoomRepo\RoomRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomRepo implementations.
 */
abstract class RoomRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomRepo implementation.
     *
     * @return RoomRepo
     */
    abstract public function getTestInstance(): RoomRepo;

    public function test_createRoom_creates_room(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $name = 'Test Room';
        $purpose = 'Testing';

        $room = $repo->createRoom($user_id, $name, $purpose);

        $this->assertInstanceOf(Room::class, $room);
        $this->assertSame($user_id, $room->owner_user_id);
        $this->assertSame($name, $room->name);
        $this->assertSame($purpose, $room->purpose);
    }

    public function test_getRoomById_returns_null_initially(): void
    {
        $repo = $this->getTestInstance();

        $room = $repo->getRoomById('nonexistent_id');

        $this->assertNull($room);
    }

    public function test_getRoomById_returns_created_room(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $name = 'Test Room';
        $purpose = 'Testing';

        $createdRoom = $repo->createRoom($user_id, $name, $purpose);
        $retrievedRoom = $repo->getRoomById($createdRoom->id);

        $this->assertNotNull($retrievedRoom);
        $this->assertSame($createdRoom->id, $retrievedRoom->id);
        $this->assertSame($name, $retrievedRoom->name);
    }

    public function test_getAllRooms_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $rooms = $repo->getAllRooms();

        $this->assertIsArray($rooms);
        // Note: PDO tests may have existing data, so we don't assert empty
        foreach ($rooms as $room) {
            $this->assertInstanceOf(\Bristolian\Model\Generated\Room::class, $room);
        }
    }

    public function test_getAllRooms_returns_all_created_rooms(): void
    {
        $repo = $this->getTestInstance();

        $room1 = $repo->createRoom('user_1', 'Room 1', 'Purpose 1');
        $room2 = $repo->createRoom('user_2', 'Room 2', 'Purpose 2');

        $rooms = $repo->getAllRooms();

        $this->assertCount(2, $rooms);
        $this->assertContainsOnlyInstancesOf(Room::class, $rooms);
    }
}

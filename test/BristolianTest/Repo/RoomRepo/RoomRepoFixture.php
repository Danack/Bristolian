<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use PHPUnit\Framework\Attributes\CoversMethod;
use BristolianGenerated\Model\Room;
use Bristolian\Repo\RoomRepo\RoomRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomRepo implementations.
 *
 * Scenario data (user id) is provided by concrete tests via getValidUserId().
 *
 */

#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'createRoom')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'getAllRooms')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'getLatestRoomsCreated')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'getRoomById')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'getRoomByName')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\FakeRoomRepo::class, 'updateRoomNameAndPurpose')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\PdoRoomRepo::class, '__construct')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\PdoRoomRepo::class, 'createRoom')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\PdoRoomRepo::class, 'getAllRooms')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\PdoRoomRepo::class, 'getLatestRoomsCreated')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\PdoRoomRepo::class, 'getRoomById')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\PdoRoomRepo::class, 'getRoomByName')]
#[CoversMethod(\Bristolian\Repo\RoomRepo\PdoRoomRepo::class, 'updateRoomNameAndPurpose')]

abstract class RoomRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomRepo implementation.
     *
     * @return RoomRepo
     */
    abstract public function getTestInstance(): RoomRepo;

    /**
     * A user id that exists in this implementation's world (for FK-safe tests).
     */
    abstract protected function getValidUserId(): string;

    public function test_createRoom_creates_room(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getValidUserId();
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

        $user_id = $this->getValidUserId();
        $name = 'Test Room';
        $purpose = 'Testing';

        $createdRoom = $repo->createRoom($user_id, $name, $purpose);
        $retrievedRoom = $repo->getRoomById($createdRoom->id);

        $this->assertNotNull($retrievedRoom);
        $this->assertSame($createdRoom->id, $retrievedRoom->id);
        $this->assertSame($name, $retrievedRoom->name);
    }

    public function test_updateRoomNameAndPurpose_updates_stored_room(): void
    {
        $repo = $this->getTestInstance();

        $user_id = $this->getValidUserId();
        $createdRoom = $repo->createRoom($user_id, 'Original Name', 'Original purpose');

        $repo->updateRoomNameAndPurpose($createdRoom->id, 'New Name', 'New purpose');

        $updated = $repo->getRoomById($createdRoom->id);
        $this->assertNotNull($updated);
        $this->assertSame('New Name', $updated->name);
        $this->assertSame('New purpose', $updated->purpose);
        $this->assertSame($createdRoom->owner_user_id, $updated->owner_user_id);
    }

    public function test_getAllRooms_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $rooms = $repo->getAllRooms();

        // Note: PDO tests may have existing data, so we don't assert empty
        foreach ($rooms as $room) {
            $this->assertInstanceOf(\BristolianGenerated\Model\Room::class, $room);
        }
    }

    public function test_getAllRooms_respects_default_limit(): void
    {
        $repo = $this->getTestInstance();

        $userId = $this->getValidUserId();
        $room1 = $repo->createRoom($userId, 'Room 1', 'Purpose 1');
        $room2 = $repo->createRoom($userId, 'Room 2', 'Purpose 2');

        $rooms = $repo->getAllRooms();

        $this->assertGreaterThanOrEqual(2, count($rooms));
        $this->assertLessThanOrEqual(RoomRepo::DEFAULT_LIST_LIMIT, count($rooms));
    }

    public function test_getLatestRoomsCreated_includes_recently_created_rooms(): void
    {
        $repo = $this->getTestInstance();

        $userId = $this->getValidUserId();
        $room1 = $repo->createRoom($userId, 'Room 1', 'Purpose 1');
        $room2 = $repo->createRoom($userId, 'Room 2', 'Purpose 2');

        $rooms = $repo->getLatestRoomsCreated();

        $this->assertGreaterThanOrEqual(2, count($rooms));
        $this->assertLessThanOrEqual(RoomRepo::DEFAULT_LIST_LIMIT, count($rooms));
        $roomIds = array_map(fn(Room $r) => $r->id, $rooms);
        $this->assertContains($room1->id, $roomIds);
        $this->assertContains($room2->id, $roomIds);
        $this->assertSame($room2->id, $rooms[0]->id);
    }

    public function test_getRoomByName_returns_empty_when_no_match(): void
    {
        $repo = $this->getTestInstance();

        $rooms = $repo->getRoomByName('Nonexistent Room Name ' . bin2hex(random_bytes(4)));

        $this->assertSame([], $rooms);
    }

    public function test_getRoomByName_returns_all_rooms_with_that_name(): void
    {
        $repo = $this->getTestInstance();

        $userId = $this->getValidUserId();
        $sharedName = 'Shared Name ' . bin2hex(random_bytes(4));
        $room1 = $repo->createRoom($userId, $sharedName, 'Purpose A');
        $room2 = $repo->createRoom($userId, $sharedName, 'Purpose B');

        $rooms = $repo->getRoomByName($sharedName);

        $this->assertCount(2, $rooms);
        $roomIds = array_map(fn(Room $r) => $r->id, $rooms);
        $this->assertContains($room1->id, $roomIds);
        $this->assertContains($room2->id, $roomIds);
    }
}

<?php

namespace BristolianTest\Repo\UserSearch;

use Bristolian\Repo\RoomRepo\PdoRoomRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use Room;

/**
 * @coversNothing
 */
class PdoRoomRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\RoomRepo\PdoRoomRepo
     * @return void
     * @throws \DI\InjectionException
     */
    public function testWorks()
    {
        $adminUser = $this->createTestAdminUser();

        $pdoRoomRepo = $this->injector->make(PdoRoomRepo::class);

        $room_name = $this->getTestRoomName();
        $room_description = $this->getTestRoomDescription();

        $room_created = $pdoRoomRepo->createRoom(
            $adminUser->getUserId(),
            $room_name,
            $room_description
        );

        $room_from_db = $pdoRoomRepo->getRoomById($room_created->getRoomId());
        $this->assertEquals($room_created, $room_from_db);

        $rooms = $pdoRoomRepo->getAllRooms();
        foreach ($rooms as $room) {
            $this->assertInstanceOf(Room::class, $room);
        }
    }
}

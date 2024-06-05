<?php

namespace BristolianTest\Repo\UserSearch;

use Bristolian\DataType\CreateUserParams;
use Bristolian\Repo\AdminRepo\PdoAdminRepo;
use Bristolian\Repo\DbInfo\PdoDbInfo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use Bristolian\Repo\UserSearch\PdoUserSearch;
use Bristolian\Repo\UserSearch\UserSearch;
use Bristolian\Repo\RoomRepo\PdoRoomRepo;

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
        $room_name = 'test_room' . time() . '_' . random_int(1000, 9999);
        $room_description = 'test_room_description' . time() . '_' . random_int(1000, 9999);

        $room_created = $pdoRoomRepo->createRoom(
            $adminUser->getUserId(),
            $room_name,
            $room_description
        );

        $room_from_db = $pdoRoomRepo->getRoomById($room_created->getRoomId());
        $this->assertEquals($room_created, $room_from_db);
    }
}

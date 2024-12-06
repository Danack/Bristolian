<?php

namespace BristolianTest\Repo\RoomFileRepo;

use BristolianTest\BaseTestCase;
use Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo;
use BristolianTest\Repo\TestPlaceholders;

/**
 * @coversNothing
 */
class PdoRoomFileRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo
     */
    public function testWorks()
    {

        [$room, $user] = $this->createTestUserAndRoom();
        $file_id = $this->createTestFile($user);
        $roomFileRepo = $this->injector->make(PdoRoomFileRepo::class);

        // Check room has no files listed
        $files = $roomFileRepo->getFilesForRoom($room->getRoomId());
        $this->assertEmpty($files);

        // Check adding files works
        $roomFileRepo->addFileToRoom($file_id, $room->getRoomId());
//        $roomFileRepo->addFileToRoom($file_id_2, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room->getRoomId());
        $this->assertCount(1, $files);

        // Check other room still has no files listed
        $files = $roomFileRepo->getFilesForRoom("some other room");
        $this->assertEmpty($files);
    }
}

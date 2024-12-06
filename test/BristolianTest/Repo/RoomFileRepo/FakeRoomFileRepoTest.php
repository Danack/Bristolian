<?php

namespace BristolianTest\Repo\RoomFileRepo;

use BristolianTest\BaseTestCase;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;

/**
 * @coversNothing
 */
class FakeRoomFileRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function testWorks()
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = '123456';
        $file_id_1 = '2345678';
        $file_id_2 = '23456781011';

        // Check room has no files listed
        $files = $roomFileRepo->getFilesForRoom($room_id);
        $this->assertEmpty($files);

        // Check adding files works
        $roomFileRepo->addFileToRoom($file_id_1, $room_id);
        $roomFileRepo->addFileToRoom($file_id_2, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room_id);
        $this->assertCount(2, $files);

        // Check other room still has no files listed
        $files = $roomFileRepo->getFilesForRoom("some other room");
        $this->assertEmpty($files);
    }
}

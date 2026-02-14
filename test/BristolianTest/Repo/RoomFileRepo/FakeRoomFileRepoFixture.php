<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileRepo;

use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;

/**
 * Tests for FakeRoomFileRepo
 *
 * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
 * @group standard_repo
 */
class FakeRoomFileRepoFixture extends RoomFileRepoFixture
{
    /**
     * @return RoomFileRepo
     */
    public function getTestInstance(): RoomFileRepo
    {
        return new FakeRoomFileRepo();
    }

    protected function getValidRoomId(): string
    {
        return 'room_456';
    }

    protected function getValidFileId(): string
    {
        return 'file_123';
    }
    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_constructor(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();
        $this->assertInstanceOf(FakeRoomFileRepo::class, $roomFileRepo);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFilesForRoom_returns_empty_initially(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $files = $roomFileRepo->getFilesForRoom('room_123');

        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_addFileToRoom_and_getFilesForRoom(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id_1 = 'file_1';
        $file_id_2 = 'file_2';

        // Check room has no files listed
        $files = $roomFileRepo->getFilesForRoom($room_id);
        $this->assertEmpty($files);

        // Check adding files works
        $roomFileRepo->addFileToRoom($file_id_1, $room_id);
        $roomFileRepo->addFileToRoom($file_id_2, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room_id);
        $this->assertCount(2, $files);

        // Check other room still has no files listed
        $files = $roomFileRepo->getFilesForRoom("some_other_room");
        $this->assertEmpty($files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFilesForRoom_returns_stored_files(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room_id);

        $this->assertCount(1, $files);
        $this->assertInstanceOf(RoomFileObjectInfo::class, $files[0]);
        $this->assertSame($file_id, $files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_files_in_different_rooms_are_independent(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id_1 = 'room_1';
        $room_id_2 = 'room_2';
        $file_id_1 = 'file_1';
        $file_id_2 = 'file_2';

        $roomFileRepo->addFileToRoom($file_id_1, $room_id_1);
        $roomFileRepo->addFileToRoom($file_id_2, $room_id_2);

        $room1_files = $roomFileRepo->getFilesForRoom($room_id_1);
        $room2_files = $roomFileRepo->getFilesForRoom($room_id_2);

        $this->assertCount(1, $room1_files);
        $this->assertCount(1, $room2_files);
        $this->assertSame($file_id_1, $room1_files[0]->id);
        $this->assertSame($file_id_2, $room2_files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_file(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);

        $fileDetails = $roomFileRepo->getFileDetails($room_id, $file_id);

        $this->assertInstanceOf(RoomFileObjectInfo::class, $fileDetails);
        $this->assertSame($file_id, $fileDetails->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_nonexistent_file(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $fileDetails = $roomFileRepo->getFileDetails('room_123', 'nonexistent_file');

        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_nonexistent_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $fileDetails = $roomFileRepo->getFileDetails('nonexistent_room', 'file_1');

        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_returns_null_for_file_in_different_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id_1 = 'room_1';
        $room_id_2 = 'room_2';
        $file_id = 'file_1';

        // Add file to room_1
        $roomFileRepo->addFileToRoom($file_id, $room_id_1);

        // Try to get file from room_2
        $fileDetails = $roomFileRepo->getFileDetails($room_id_2, $file_id);

        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_same_file_in_multiple_rooms(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id_1 = 'room_1';
        $room_id_2 = 'room_2';
        $file_id = 'file_1';

        // Add same file to both rooms
        $roomFileRepo->addFileToRoom($file_id, $room_id_1);
        $roomFileRepo->addFileToRoom($file_id, $room_id_2);

        $room1_files = $roomFileRepo->getFilesForRoom($room_id_1);
        $room2_files = $roomFileRepo->getFilesForRoom($room_id_2);

        // Both rooms should have the file
        $this->assertCount(1, $room1_files);
        $this->assertCount(1, $room2_files);
        $this->assertSame($file_id, $room1_files[0]->id);
        $this->assertSame($file_id, $room2_files[0]->id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_stored_file_properties(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);
        $files = $roomFileRepo->getFilesForRoom($room_id);

        $file = $files[0];
        $this->assertInstanceOf(\DateTimeInterface::class, $file->created_at);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_multiple_files_in_room(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';

        $roomFileRepo->addFileToRoom('file_1', $room_id);
        $roomFileRepo->addFileToRoom('file_2', $room_id);
        $roomFileRepo->addFileToRoom('file_3', $room_id);

        $files = $roomFileRepo->getFilesForRoom($room_id);

        $this->assertCount(3, $files);
        $this->assertContainsOnlyInstancesOf(RoomFileObjectInfo::class, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo
     */
    public function test_getFileDetails_matches_getFilesForRoom(): void
    {
        $roomFileRepo = new FakeRoomFileRepo();

        $room_id = 'room_123';
        $file_id = 'file_1';

        $roomFileRepo->addFileToRoom($file_id, $room_id);

        $files_list = $roomFileRepo->getFilesForRoom($room_id);
        $file_from_list = $files_list[0];

        $file_details = $roomFileRepo->getFileDetails($room_id, $file_id);

        // Should be the same object
        $this->assertSame($file_from_list, $file_details);
    }
}

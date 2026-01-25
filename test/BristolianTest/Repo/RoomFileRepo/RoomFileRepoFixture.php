<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileRepo;

use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomFileRepo implementations.
 */
abstract class RoomFileRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomFileRepo implementation.
     *
     * @return RoomFileRepo
     */
    abstract public function getTestInstance(): RoomFileRepo;

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::addFileToRoom
     */
    public function test_addFileToRoom(): void
    {
        $repo = $this->getTestInstance();

        $fileStorageId = 'file_123';
        $room_id = 'room_456';

        // Should not throw an exception
        $repo->addFileToRoom($fileStorageId, $room_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::getFilesForRoom
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::addFileToRoom
     */
    public function test_getFilesForRoom_returns_files_after_adding(): void
    {
        $repo = $this->getTestInstance();

        $fileStorageId = 'file_123';
        $room_id = 'room_456';

        $repo->addFileToRoom($fileStorageId, $room_id);

        $files = $repo->getFilesForRoom($room_id);
        $this->assertNotEmpty($files);
        $this->assertContainsOnlyInstancesOf(RoomFileObjectInfo::class, $files);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::getFileDetails
     */
    public function test_getFileDetails_returns_null_for_nonexistent_file(): void
    {
        $repo = $this->getTestInstance();

        $room_id = 'room_456';
        $file_id = 'nonexistent_file';

        $fileDetails = $repo->getFileDetails($room_id, $file_id);
        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::getFileDetails
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::addFileToRoom
     */
    public function test_getFileDetails_returns_file_after_adding(): void
    {
        $repo = $this->getTestInstance();

        $fileStorageId = 'file_123';
        $room_id = 'room_456';

        $repo->addFileToRoom($fileStorageId, $room_id);

        $fileDetails = $repo->getFileDetails($room_id, $fileStorageId);
        $this->assertNotNull($fileDetails);
        $this->assertInstanceOf(RoomFileObjectInfo::class, $fileDetails);
    }
}

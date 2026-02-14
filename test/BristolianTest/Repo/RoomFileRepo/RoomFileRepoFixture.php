<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileRepo;

use Bristolian\Model\Generated\RoomFileObjectInfo;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomFileRepo implementations.
 *
 * Scenario data (room id, file id) is provided by concrete tests via getValidRoomId()
 * and getValidFileId() so the fixture stays schema-agnostic. See
 * docs/refactoring/default_test_scenarios_and_worlds.md § Abstract repo fixtures.
 *
 * @coversNothing
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
     * A room id that exists in this implementation's world (for FK-safe tests).
     */
    abstract protected function getValidRoomId(): string;

    /**
     * A file id that exists in this implementation's world and can be added to a room.
     */
    abstract protected function getValidFileId(): string;

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::addFileToRoom
     */
    public function test_addFileToRoom(): void
    {
        $repo = $this->getTestInstance();

        $fileStorageId = $this->getValidFileId();
        $room_id = $this->getValidRoomId();

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

        $fileStorageId = $this->getValidFileId();
        $room_id = $this->getValidRoomId();

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

        $room_id = $this->getValidRoomId();
        $file_id = 'nonexistent_file';

        $fileDetails = $repo->getFileDetails($room_id, $file_id);
        $this->assertNull($fileDetails);
    }

    /**
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::getFileDetails
     * @covers \Bristolian\Repo\RoomFileRepo\RoomFileRepo::addFileToRoom
     * @group wip
     */
    public function test_getFileDetails_returns_file_after_adding(): void
    {
        $repo = $this->getTestInstance();

        $fileStorageId = $this->getValidFileId();
        $room_id = $this->getValidRoomId();

        $repo->addFileToRoom($fileStorageId, $room_id);

        $fileDetails = $repo->getFileDetails($room_id, $fileStorageId);
        $this->assertNotNull($fileDetails);
        $this->assertInstanceOf(RoomFileObjectInfo::class, $fileDetails);
    }
}

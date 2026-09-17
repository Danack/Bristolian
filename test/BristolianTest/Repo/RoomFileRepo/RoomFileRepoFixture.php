<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileRepo;

use PHPUnit\Framework\Attributes\CoversMethod;
use BristolianGenerated\Model\RoomFileObjectInfo;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Repo\RoomFileRepo\RoomFileRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomFileRepo implementations.
 *
 * Scenario data (room id, file id) is provided by concrete tests via getValidRoomId()
 * and getValidFileId() so the fixture stays schema-agnostic. See
 * notes/refactoring/default_test_scenarios_and_worlds.md § Abstract repo fixtures.
 *
 */

#[CoversMethod(\Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo::class, 'addFileToRoom')]
#[CoversMethod(\Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::class, '__construct')]
#[CoversMethod(\Bristolian\Repo\RoomFileRepo\PdoRoomFileRepo::class, 'addFileToRoom')]

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

    public function test_addFileToRoom(): void
    {
        $repo = $this->getTestInstance();

        $fileStorageId = $this->getValidFileId();
        $room_id = $this->getValidRoomId();

        // Should not throw an exception
        $repo->addFileToRoom($fileStorageId, $room_id);
    }

    public function test_getFilesForRoom_returns_files_after_adding(): void
    {
        $repo = $this->getTestInstance();

        $fileStorageId = $this->getValidFileId();
        $room_id = $this->getValidRoomId();

        $repo->addFileToRoom($fileStorageId, $room_id);

        $files = $repo->getFilesForRoom($room_id, RoomContentSearchParams::default());
        $this->assertNotEmpty($files);
    }

    public function test_getFileDetails_returns_null_for_nonexistent_file(): void
    {
        $repo = $this->getTestInstance();

        $room_id = $this->getValidRoomId();
        $file_id = 'nonexistent_file';

        $fileDetails = $repo->getFileDetails($room_id, $file_id);
        $this->assertNull($fileDetails);
    }

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

<?php

declare(strict_types=1);

namespace BristolianTest\Service\RoomFileStorage;

use Bristolian\Service\RoomFileStorage\FakeRoomFileStorage;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeRoomFileStorageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\RoomFileStorage\FakeRoomFileStorage::__construct
     * @covers \Bristolian\Service\RoomFileStorage\FakeRoomFileStorage::storeFileForRoomAndUser
     */
    public function test_storeFileForRoomAndUser_returns_configured_file_id(): void
    {
        $fileId = 'roomfile_abc';
        $storage = new FakeRoomFileStorage($fileId);
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../sample.pdf');

        $result = $storage->storeFileForRoomAndUser('user1', 'room1', $uploadedFile);

        $this->assertSame($fileId, $result);
    }

    /**
     * @covers \Bristolian\Service\RoomFileStorage\FakeRoomFileStorage::storeFileForRoomAndUser
     */
    public function test_storeFileForRoomAndUser_returns_configured_error(): void
    {
        $error = UploadError::unsupportedFileType();
        $storage = new FakeRoomFileStorage($error);
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../sample.pdf');

        $result = $storage->storeFileForRoomAndUser('user1', 'room1', $uploadedFile);

        $this->assertSame($error, $result);
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\Service\RoomFileStorage;

use Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileRepo\FakeRoomFileRepo;
use Bristolian\Service\ObjectStore\FakeRoomFileObjectStore;
use Bristolian\Service\RoomFileStorage\StandardRoomFileStorage;
use Bristolian\Service\RoomFileStorage\UploadError;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class StandardRoomFileStorageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\RoomFileStorage\StandardRoomFileStorage::__construct
     * @covers \Bristolian\Service\RoomFileStorage\StandardRoomFileStorage::storeFileForRoomAndUser
     */
    public function test_storeFileForRoomAndUser_returns_file_id_and_uploads_to_object_store(): void
    {
        $imagePath = __DIR__ . '/../../../fixtures/images/invalid_avatar.jpg';
        if (!is_readable($imagePath)) {
            $this->markTestSkipped('Fixture not found: ' . $imagePath);
        }
        $fileObjectStore = new FakeRoomFileObjectStore();
        $roomFileObjectInfoRepo = new FakeRoomFileObjectInfoRepo();
        $roomFileRepo = new FakeRoomFileRepo();

        $storage = new StandardRoomFileStorage($fileObjectStore, $roomFileObjectInfoRepo, $roomFileRepo);
        $uploadedFile = UploadedFile::fromFile($imagePath);

        $result = $storage->storeFileForRoomAndUser('user_1', 'room_1', $uploadedFile);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        $storedFileInfo = $roomFileObjectInfoRepo->getStoredFileInfo();
        $this->assertCount(1, $storedFileInfo);
        $info = array_values($storedFileInfo)[0];
        $this->assertMatchesRegularExpression('/\.(jpeg|jpg)$/', $info->normalized_name);
        $this->assertTrue($fileObjectStore->hasFile($info->normalized_name));
        $filesInRoom = $roomFileRepo->getFilesForRoom('room_1');
        $this->assertCount(1, $filesInRoom);
    }

    /**
     * @covers \Bristolian\Service\RoomFileStorage\StandardRoomFileStorage::storeFileForRoomAndUser
     */
    public function test_storeFileForRoomAndUser_returns_uploadError_when_extension_not_allowed(): void
    {
        $fileObjectStore = new FakeRoomFileObjectStore();
        $roomFileObjectInfoRepo = new FakeRoomFileObjectInfoRepo();
        $roomFileRepo = new FakeRoomFileRepo();
        $storage = new StandardRoomFileStorage($fileObjectStore, $roomFileObjectInfoRepo, $roomFileRepo);
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        $result = $storage->storeFileForRoomAndUser('user_1', 'room_1', $uploadedFile);

        $this->assertInstanceOf(UploadError::class, $result);
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $result->error_message);
    }

    /**
     * @covers \Bristolian\Service\RoomFileStorage\StandardRoomFileStorage::storeFileForRoomAndUser
     */
    public function test_storeFileForRoomAndUser_returns_uploadError_when_file_unreadable(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'roomfile_test_') . '.txt';
        file_put_contents($tempFile, 'content');
        \Safe\chmod($tempFile, 0o000);
        try {
            if (@file_get_contents($tempFile) !== false) {
                $this->markTestSkipped('chmod 0o000 does not prevent read in this environment');
            }
            $fileObjectStore = new FakeRoomFileObjectStore();
            $roomFileObjectInfoRepo = new FakeRoomFileObjectInfoRepo();
            $roomFileRepo = new FakeRoomFileRepo();
            $storage = new StandardRoomFileStorage($fileObjectStore, $roomFileObjectInfoRepo, $roomFileRepo);
            $uploadedFile = UploadedFile::fromFile($tempFile);

            $result = $storage->storeFileForRoomAndUser('user_1', 'room_1', $uploadedFile);

            $this->assertInstanceOf(UploadError::class, $result);
            $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $result->error_message);
        } finally {
            @chmod($tempFile, 0o600);
            @unlink($tempFile);
        }
    }
}

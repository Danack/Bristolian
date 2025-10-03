<?php

namespace BristolianTest\Service\FileStorageProcessor;

use Bristolian\Service\ObjectStore\FakeRoomFileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
//use Bristolian\Service\FileStorageProcessor\StandardFileStorageProcessor;
use Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo;
use Bristolian\Service\FileStorageProcessor\ObjectStoredFileInfo;
use Bristolian\Service\RoomFileStorage\UploadError;

/**
 * @coversNothing
 */
class StandardFileStorageProcessorTest extends BaseTestCase
{
    public function testWorks()
    {
        $this->markTestSkipped("Middle of refactoring");

//        $fileStorageInfoRepo = new FakeRoomFileObjectInfoRepo();
//        $uploadedFile = UploadedFile::fromFile(__FILE__);
//        $objectStore = new FakeRoomFileObjectStore();
//
//        $storage_processor = new StandardFileStorageProcessor($fileStorageInfoRepo);
//
//        $result = $storage_processor->storeFileForUser(
//            $user_id = '12345',
//            $uploadedFile,
//            $allowedExtensions = ["php"],
//            $objectStore
//        );
//
//        $this->assertInstanceOf(ObjectStoredFileInfo::class, $result);
//        $this->assertTrue($objectStore->hasFile($result->normalized_filename));
    }

    public function testErrors_unreadable_file()
    {
        $this->markTestSkipped("Middle of refactoring");
//        $fileStorageInfoRepo = new FakeRoomFileObjectInfoRepo();
//        $uploadedFile = UploadedFile::fromFile(__DIR__ . "/test_unreadable.txt");
//        \Safe\chmod(__DIR__ . "/test_unreadable.txt", 0o055);
//        $objectStore = new FakeRoomFileObjectStore();
//
//        $storage_processor = new StandardFileStorageProcessor($fileStorageInfoRepo);
//
//        $result = $storage_processor->storeFileForUser(
//            $user_id = '12345',
//            $uploadedFile,
//            $allowedExtensions = ["php"],
//            $objectStore
//        );
//        \Safe\chmod(__DIR__ . "/test_unreadable.txt", 0o755);
//
//        $this->assertInstanceOf(UploadError::class, $result);
//        $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $result->error_message);
    }


    public function testErrors_unsupported_file_type()
    {
        $this->markTestSkipped("Middle of refactoring");
//        $fileStorageInfoRepo = new FakeRoomFileObjectInfoRepo();
//        $uploadedFile = UploadedFile::fromFile(__FILE__);
//
//
//        $objectStore = new FakeRoomFileObjectStore();
//
//        $storage_processor = new StandardFileStorageProcessor($fileStorageInfoRepo);
//
//        $result = $storage_processor->storeFileForUser(
//            $user_id = '12345',
//            $uploadedFile,
//            $allowedExtensions = ["pdf"],
//            $objectStore
//        );
//        $this->assertInstanceOf(UploadError::class, $result);
//        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $result->error_message);
    }
}

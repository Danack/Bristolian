<?php

namespace BristolianTest\Service\FileStorageProcessor;

use Bristolian\Service\ObjectStore\FakeRoomFileObjectStore;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use Bristolian\Service\FileStorageProcessor\FakeWorksFileStorageProcessor;
use Bristolian\Service\FileStorageProcessor\ObjectStoredFileInfo;

/**
 * @covers \Bristolian\Service\FileStorageProcessor\FakeWorksFileStorageProcessor
 * @group wip
 */
class FakeWorksFileStorageProcessorTest extends BaseTestCase
{
    public function testWorks()
    {
        $storageProcessor = new FakeWorksFileStorageProcessor();

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $objectStore = new FakeRoomFileObjectStore();

        $result = $storageProcessor->storeFileForUser(
            $user_id = '12345',
            $uploadedFile,
            $allowedExtensions = ["php"],
            $objectStore
        );

        $this->assertInstanceOf(ObjectStoredFileInfo::class, $result);
    }
}
<?php

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Service\ObjectStore\FakeRoomFileObjectStore;
use Bristolian\Service\ObjectStore\FileObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use Bristolian\Service\MemeStorageProcessor\FakeWorksMemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;

/**
 * @covers \Bristolian\Service\MemeStorageProcessor\FakeWorksMemeStorageProcessor
 * @group wip
 */
class FakeWorksMemeStorageProcessorTest extends BaseTestCase
{
    public function testWorks()
    {
        $storageProcessor = new FakeWorksMemeStorageProcessor();

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $objectStore = new FakeRoomFileObjectStore();

        $result = $storageProcessor->storeMemeForUser(
            $user_id = '12345',
            $uploadedFile,
            $allowedExtensions = ["php"],
            $objectStore
        );

        $this->assertInstanceOf(ObjectStoredMeme::class, $result);
    }
}

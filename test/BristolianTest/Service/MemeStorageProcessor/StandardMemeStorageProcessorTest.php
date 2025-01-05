<?php

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Service\ObjectStore\FakeMemeObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor;
use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;
use Bristolian\Service\MemeStorageProcessor\UploadError;

/**
 * @covers \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor
 */
class StandardMemeStorageProcessorTest extends BaseTestCase
{
    public function testWorks()
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $objectStore = new FakeMemeObjectStore();

        $storage_processor = new StandardMemeStorageProcessor($memeStorageRepo);

        $result = $storage_processor->storeMemeForUser(
            $user_id = '12345',
            $uploadedFile,
            $allowedExtensions = ["php"],
            $objectStore
        );

        $this->assertInstanceOf(ObjectStoredMeme::class, $result);
        $this->assertTrue($objectStore->hasFile($result->normalized_filename));
    }

    public function testErrors_unreadable_file()
    {
        $fileStorageInfoRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__DIR__ . "/test_unreadable.txt");
        \Safe\chmod(__DIR__ . "/test_unreadable.txt", 0o055);
        $objectStore = new FakeMemeObjectStore();

        $storage_processor = new StandardMemeStorageProcessor($fileStorageInfoRepo);

        $result = $storage_processor->storeMemeForUser(
            $user_id = '12345',
            $uploadedFile,
            $allowedExtensions = ["php"],
            $objectStore
        );
        \Safe\chmod(__DIR__ . "/test_unreadable.txt", 0o755);

        $this->assertInstanceOf(UploadError::class, $result);
        $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $result->error_message);
    }


    public function testErrors_unsupported_file_type()
    {
        $fileStorageInfoRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);


        $objectStore = new FakeMemeObjectStore();

        $storage_processor = new StandardMemeStorageProcessor($fileStorageInfoRepo);

        $result = $storage_processor->storeMemeForUser(
            $user_id = '12345',
            $uploadedFile,
            $allowedExtensions = ["pdf"],
            $objectStore
        );
        $this->assertInstanceOf(UploadError::class, $result);
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $result->error_message);
    }
}

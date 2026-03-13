<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;
use Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\UploadError;
use Bristolian\Service\ObjectStore\FakeMemeObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class StandardMemeStorageProcessorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::__construct
     * @covers \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::storeMemeForUser
     */
    public function test_storeMemeForUser_returns_ObjectStoredMeme_and_uploads_to_object_store(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $objectStore = new FakeMemeObjectStore();

        $storageProcessor = new StandardMemeStorageProcessor($memeStorageRepo);

        $result = $storageProcessor->storeMemeForUser(
            '12345',
            $uploadedFile,
            ['php'],
            $objectStore
        );

        $this->assertInstanceOf(ObjectStoredMeme::class, $result);
        $this->assertStringEndsWith('.php', $result->normalized_filename);
        $this->assertTrue($objectStore->hasFile($result->normalized_filename));
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::storeMemeForUser
     */
    public function test_storeMemeForUser_returns_duplicateOriginalFilename_when_user_already_has_file(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $objectStore = new FakeMemeObjectStore();
        $storageProcessor = new StandardMemeStorageProcessor($memeStorageRepo);

        $first = $storageProcessor->storeMemeForUser('12345', $uploadedFile, ['php'], $objectStore);
        $this->assertInstanceOf(ObjectStoredMeme::class, $first);

        $second = $storageProcessor->storeMemeForUser('12345', $uploadedFile, ['php'], $objectStore);
        $this->assertInstanceOf(UploadError::class, $second);
        $this->assertSame(UploadError::DUPLICATE_FILENAME, $second->error_code);
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::storeMemeForUser
     */
    public function test_storeMemeForUser_returns_uploadError_when_file_unreadable(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'meme_test_') . '.txt';
        file_put_contents($tempFile, 'content');
        \Safe\chmod($tempFile, 0o000);
        try {
            if (@file_get_contents($tempFile) !== false) {
                $this->markTestSkipped('chmod 0o000 does not prevent read in this environment');
            }
            $memeStorageRepo = new FakeMemeStorageRepo();
            $uploadedFile = UploadedFile::fromFile($tempFile);
            $objectStore = new FakeMemeObjectStore();
            $storageProcessor = new StandardMemeStorageProcessor($memeStorageRepo);

            $result = $storageProcessor->storeMemeForUser(
                '12345',
                $uploadedFile,
                ['txt'],
                $objectStore
            );

            $this->assertInstanceOf(UploadError::class, $result);
            $this->assertSame(UploadError::UNREADABLE_FILE_MESSAGE, $result->error_message);
        } finally {
            @chmod($tempFile, 0o600);
            @unlink($tempFile);
        }
    }

    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\StandardMemeStorageProcessor::storeMemeForUser
     */
    public function test_storeMemeForUser_returns_uploadError_when_extension_not_allowed(): void
    {
        $memeStorageRepo = new FakeMemeStorageRepo();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $objectStore = new FakeMemeObjectStore();
        $storageProcessor = new StandardMemeStorageProcessor($memeStorageRepo);

        $result = $storageProcessor->storeMemeForUser(
            '12345',
            $uploadedFile,
            ['pdf'],
            $objectStore
        );

        $this->assertInstanceOf(UploadError::class, $result);
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $result->error_message);
    }
}

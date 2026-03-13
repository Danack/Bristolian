<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeStorageProcessor;

use Bristolian\Service\MemeStorageProcessor\FakeWorksMemeStorageProcessor;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;
use Bristolian\Service\ObjectStore\FakeMemeObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeWorksMemeStorageProcessorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\MemeStorageProcessor\FakeWorksMemeStorageProcessor::storeMemeForUser
     */
    public function test_storeMemeForUser_returns_ObjectStoredMeme(): void
    {
        $storageProcessor = new FakeWorksMemeStorageProcessor();
        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $objectStore = new FakeMemeObjectStore();

        $result = $storageProcessor->storeMemeForUser(
            '12345',
            $uploadedFile,
            ['php'],
            $objectStore
        );

        $this->assertInstanceOf(ObjectStoredMeme::class, $result);
        $this->assertStringEndsWith('.pdf', $result->normalized_filename);
        $this->assertNotEmpty($result->meme_id);
    }
}

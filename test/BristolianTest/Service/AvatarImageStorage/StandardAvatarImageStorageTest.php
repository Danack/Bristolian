<?php

declare(strict_types=1);

namespace BristolianTest\Service\AvatarImageStorage;

use Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo;
use Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage;
use Bristolian\Service\AvatarImageStorage\UploadError;
use Bristolian\Service\ObjectStore\FakeAvatarImageObjectStore;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage
 */
class StandardAvatarImageStorageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage::__construct
     * @covers \Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage::storeAvatarForUser
     */
    public function test_storeAvatarForUser_returns_extensionNotAllowed_when_extension_not_in_list(): void
    {
        $infoRepo = new FakeAvatarImageStorageInfoRepo();
        $objectStore = new FakeAvatarImageObjectStore();
        $storage = new StandardAvatarImageStorage($infoRepo, $objectStore);

        $uploadedFile = UploadedFile::fromFile(__FILE__);
        $result = $storage->storeAvatarForUser(
            'user_1',
            $uploadedFile,
            ['png']
        );

        $this->assertInstanceOf(UploadError::class, $result);
        $this->assertStringContainsString('not allowed', $result->error_message);
        $this->assertEmpty($objectStore->getStoredFiles());
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage::storeAvatarForUser
     */
    public function test_storeAvatarForUser_returns_imageTooSmall_when_image_smaller_than_512(): void
    {
        $imagePath = __DIR__ . '/../../../fixtures/images/small_avatar.jpg';
        $infoRepo = new FakeAvatarImageStorageInfoRepo();
        $objectStore = new FakeAvatarImageObjectStore();
        $storage = new StandardAvatarImageStorage($infoRepo, $objectStore);

        $uploadedFile = UploadedFile::fromFile($imagePath);
        $result = $storage->storeAvatarForUser(
            'user_1',
            $uploadedFile,
            get_supported_avatar_image_extensions()
        );

        $this->assertInstanceOf(UploadError::class, $result);
        $this->assertStringContainsString('too small', $result->error_message);
        $this->assertEmpty($objectStore->getStoredFiles());
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\StandardAvatarImageStorage::storeAvatarForUser
     */
    public function test_storeAvatarForUser_returns_avatar_image_id_and_uploads_to_object_store(): void
    {
        $imagePath = __DIR__ . '/../../../fixtures/stairs/stairs_test_c_7.jpeg';
        if (!is_readable($imagePath)) {
            $this->markTestSkipped('Fixture image not found: ' . $imagePath);
        }
        $imageSize = @getimagesize($imagePath);
        $minSize = StandardAvatarImageStorage::MINIMUM_AVATAR_SIZE;
        if ($imageSize === false || $imageSize[0] < $minSize || $imageSize[1] < $minSize) {
            $this->markTestSkipped("Fixture image must be at least {$minSize}x{$minSize} for this test");
        }

        $infoRepo = new FakeAvatarImageStorageInfoRepo();
        $objectStore = new FakeAvatarImageObjectStore();
        $storage = new StandardAvatarImageStorage($infoRepo, $objectStore);

        $uploadedFile = UploadedFile::fromFile($imagePath);
        $result = $storage->storeAvatarForUser(
            'user_1',
            $uploadedFile,
            get_supported_avatar_image_extensions()
        );

        $this->assertNotEmpty($result);
        $fileInfo = $infoRepo->getById($result);
        $this->assertNotNull($fileInfo);
        $this->assertSame($result, $fileInfo->id);
        $this->assertTrue($objectStore->hasFile($fileInfo->normalized_name));
        $contents = $objectStore->getFileContents($fileInfo->normalized_name);
        $this->assertNotEmpty($contents);
        $this->assertStringStartsWith("\xff\xd8\xff", $contents, 'Resized output should be JPEG');
    }
}

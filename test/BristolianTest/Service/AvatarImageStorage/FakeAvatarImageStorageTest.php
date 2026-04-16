<?php

declare(strict_types=1);

namespace BristolianTest\Service\AvatarImageStorage;

use Bristolian\Service\AvatarImageStorage\FakeAvatarImageStorage;
use Bristolian\Service\AvatarImageStorage\UploadError;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeAvatarImageStorageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Service\AvatarImageStorage\FakeAvatarImageStorage::__construct
     * @covers \Bristolian\Service\AvatarImageStorage\FakeAvatarImageStorage::storeAvatarForUser
     */
    public function test_storeAvatarForUser_returns_configured_avatar_image_id(): void
    {
        $avatarImageId = 'avatar_image_abc';
        $storage = new FakeAvatarImageStorage($avatarImageId);
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../fixtures/pdfs/sample.pdf');

        $result = $storage->storeAvatarForUser('user1', $uploadedFile, ['png', 'jpg']);

        $this->assertSame($avatarImageId, $result);
    }

    /**
     * @covers \Bristolian\Service\AvatarImageStorage\FakeAvatarImageStorage::storeAvatarForUser
     */
    public function test_storeAvatarForUser_returns_configured_error(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $storage = new FakeAvatarImageStorage($error);
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../fixtures/pdfs/sample.pdf');

        $result = $storage->storeAvatarForUser('user1', $uploadedFile, ['png', 'jpg']);

        $this->assertSame($error, $result);
    }
}

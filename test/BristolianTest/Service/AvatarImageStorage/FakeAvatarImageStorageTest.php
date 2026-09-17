<?php

declare(strict_types=1);

namespace BristolianTest\Service\AvatarImageStorage;

use Bristolian\Service\AvatarImageStorage\FakeAvatarImageStorage;
use Bristolian\Service\AvatarImageStorage\UploadError;
use Bristolian\UploadedFiles\UploadedFile;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Service\AvatarImageStorage\FakeAvatarImageStorage::class, '__construct')]
#[CoversMethod(\Bristolian\Service\AvatarImageStorage\FakeAvatarImageStorage::class, 'storeAvatarForUser')]

class FakeAvatarImageStorageTest extends BaseTestCase
{
    public function test_storeAvatarForUser_returns_configured_avatar_image_id(): void
    {
        $avatarImageId = 'avatar_image_abc';
        $storage = new FakeAvatarImageStorage($avatarImageId);
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../fixtures/pdfs/sample.pdf');

        $result = $storage->storeAvatarForUser('user1', $uploadedFile, ['png', 'jpg']);

        $this->assertSame($avatarImageId, $result);
    }

    public function test_storeAvatarForUser_returns_configured_error(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $storage = new FakeAvatarImageStorage($error);
        $uploadedFile = UploadedFile::fromFile(__DIR__ . '/../../../fixtures/pdfs/sample.pdf');

        $result = $storage->storeAvatarForUser('user1', $uploadedFile, ['png', 'jpg']);

        $this->assertSame($error, $result);
    }
}

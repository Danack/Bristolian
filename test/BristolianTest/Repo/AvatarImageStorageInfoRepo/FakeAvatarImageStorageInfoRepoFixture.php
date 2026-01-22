<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AvatarImageStorageInfoRepo;

use Bristolian\Model\Types\AvatarImageFile;
use Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo;
use Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * @group standard_repo
 */
class FakeAvatarImageStorageInfoRepoFixture extends AvatarImageStorageInfoRepoFixture
{
    public function getTestInstance(): AvatarImageStorageInfoRepo
    {
        return new FakeAvatarImageStorageInfoRepo();
    }

    /**
     * Test FakeAvatarImageStorageInfoRepo-specific behavior: setUploaded updates state
     * 
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::setUploaded
     */
    public function test_setUploaded_updates_file_state(): void
    {
        $repo = new FakeAvatarImageStorageInfoRepo();

        $uploadedFile = new UploadedFile('/tmp/test.png', 1024, 'test.png', 0);
        $file_id = $repo->storeFileInfo('user_123', 'normalized.png', $uploadedFile);

        $fileBefore = $repo->getById($file_id);
        $this->assertSame('initial', $fileBefore->state);

        $repo->setUploaded($file_id);

        $fileAfter = $repo->getById($file_id);
        $this->assertSame('uploaded', $fileAfter->state);
    }

    /**
     * Test FakeAvatarImageStorageInfoRepo-specific behavior: setUploaded throws for nonexistent file
     * 
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\FakeAvatarImageStorageInfoRepo::setUploaded
     */
    public function test_setUploaded_throws_for_nonexistent_file(): void
    {
        $repo = new FakeAvatarImageStorageInfoRepo();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to update uploaded file.');

        $repo->setUploaded('nonexistent_id');
    }
}
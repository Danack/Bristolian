<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AvatarImageStorageInfoRepo;

use Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo;
use Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo;
use Bristolian\Repo\WebPushSubscriptionRepo\UserConstraintFailedException;
use Bristolian\UploadedFiles\UploadedFile;

/**
 * @group db
 * @coversNothing
 */
class PdoAvatarImageStorageInfoRepoTest extends AvatarImageStorageInfoRepoFixture
{
    public function getTestInstance(): AvatarImageStorageInfoRepo
    {
        return $this->injector->make(PdoAvatarImageStorageInfoRepo::class);
    }

    protected function getTestUserId(): string
    {
        $adminUser = $this->createTestAdminUser();
        return $adminUser->getUserId();
    }

    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::storeFileInfo
     */
    public function test_storeFileInfo_throws_UserConstraintFailedException_when_user_does_not_exist(): void
    {
        $repo = $this->getTestInstance();

        $uploadedFile = new UploadedFile(
            '/tmp/test.png',
            1024,
            'test.png',
            0
        );

        $this->expectException(UserConstraintFailedException::class);

        $repo->storeFileInfo(
            'nonexistent-user-id',
            'test_file.png',
            $uploadedFile
        );
    }

    /**
     * @covers \Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo::setUploaded
     */
    public function test_setUploaded_throws_when_file_id_does_not_exist(): void
    {
        $repo = $this->getTestInstance();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to update uploaded file.');

        $repo->setUploaded('00000000-0000-0000-0000-000000000000');
    }
}

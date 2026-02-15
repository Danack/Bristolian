<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\AvatarImageStorageInfoRepo;

use Bristolian\Repo\AvatarImageStorageInfoRepo\AvatarImageStorageInfoRepo;
use Bristolian\Repo\AvatarImageStorageInfoRepo\PdoAvatarImageStorageInfoRepo;

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
}

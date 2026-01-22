<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserProfileRepo;

use Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;

/**
 * @group db
 */
class PdoUserProfileRepoFixture extends UserProfileRepoFixture
{
    public function getTestInstance(): UserProfileRepo
    {
        return $this->injector->make(PdoUserProfileRepo::class);
    }

    protected function getTestUserId(): string
    {
        $adminUser = $this->createTestAdminUser();
        return $adminUser->getUserId();
    }
}

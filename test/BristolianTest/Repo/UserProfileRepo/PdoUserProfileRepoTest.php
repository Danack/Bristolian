<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserProfileRepo;

use Bristolian\Repo\UserProfileRepo\PdoUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;

/**
 * @group db
 * @coversNothing
 */
class PdoUserProfileRepoTest extends UserProfileRepoFixture
{
    private ?string $cachedTestUserId = null;

    public function getTestInstance(): UserProfileRepo
    {
        return $this->injector->make(PdoUserProfileRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->cachedTestUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->cachedTestUserId = $adminUser->getUserId();
        }
        return $this->cachedTestUserId;
    }
}

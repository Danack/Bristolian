<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTextRepo;

use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Repo\MemeTextRepo\PdoMemeTextRepo;

/**
 * @group db
 */
class PdoMemeTextRepoFixture extends MemeTextRepoFixture
{
    private ?string $testUserId = null;

    public function getTestInstance(): MemeTextRepo
    {
        return $this->injector->make(PdoMemeTextRepo::class);
    }

    protected function getMemeStorageRepo(): MemeStorageRepo
    {
        return $this->injector->make(\Bristolian\Repo\MemeStorageRepo\PdoMemeStorageRepo::class);
    }

    protected function getTestUserId(): string
    {
        if ($this->testUserId === null) {
            $adminUser = $this->createTestAdminUser();
            $this->testUserId = $adminUser->getUserId();
        }
        return $this->testUserId;
    }
}

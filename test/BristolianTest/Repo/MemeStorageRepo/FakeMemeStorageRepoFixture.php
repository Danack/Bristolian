<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeMemeStorageRepoFixture extends MemeStorageRepoFixture
{
    /**
     * @return MemeStorageRepo
     */
    public function getTestInstance(): MemeStorageRepo
    {
        return new FakeMemeStorageRepo();
    }

    protected function getValidUserId(): string
    {
        return 'user_123';
    }
}

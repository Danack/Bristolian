<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeStorageRepo;

use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;

/**
 * @group standard_repo
 */
class FakeMemeStorageRepoTest extends MemeStorageRepoTest
{
    /**
     * @return MemeStorageRepo
     */
    public function getTestInstance(): MemeStorageRepo
    {
        return new FakeMemeStorageRepo();
    }
}

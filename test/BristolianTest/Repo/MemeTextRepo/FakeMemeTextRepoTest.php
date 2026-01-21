<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTextRepo;

use Bristolian\Repo\MemeStorageRepo\FakeMemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\FakeMemeTextRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;

/**
 * @group standard_repo
 */
class FakeMemeTextRepoTest extends MemeTextRepoTest
{
    private ?FakeMemeStorageRepo $memeStorageRepo = null;

    public function getTestInstance(): MemeTextRepo
    {
        // Use the same instance that tests will use via getMemeStorageRepo()
        return new FakeMemeTextRepo($this->getMemeStorageRepo());
    }

    protected function getMemeStorageRepo(): \Bristolian\Repo\MemeStorageRepo\MemeStorageRepo
    {
        // Lazy initialization - create once and reuse
        if ($this->memeStorageRepo === null) {
            $this->memeStorageRepo = new FakeMemeStorageRepo();
        }
        return $this->memeStorageRepo;
    }
}

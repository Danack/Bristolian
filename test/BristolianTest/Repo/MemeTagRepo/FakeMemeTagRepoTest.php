<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTagRepo;

use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo;

/**
 * @group standard_repo
 */
class FakeMemeTagRepoTest extends MemeTagRepoTest
{
    /**
     * @return MemeTagRepo
     */
    public function getTestInstance(): MemeTagRepo
    {
        return new FakeMemeTagRepo();
    }
}
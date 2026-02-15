<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\MemeTagRepo;

use Bristolian\Repo\MemeTagRepo\MemeTagRepo;
use Bristolian\Repo\MemeTagRepo\FakeMemeTagRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeMemeTagRepoTest extends MemeTagRepoFixture
{
    /**
     * @return MemeTagRepo
     */
    public function getTestInstance(): MemeTagRepo
    {
        return new FakeMemeTagRepo();
    }
}

<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\FakeBccTroRepo;

/**
 * @group standard_repo
 */
class FakeBccTroRepoFixture extends BccTroRepoFixture
{
    public function getTestInstance(): BccTroRepo
    {
        return new FakeBccTroRepo();
    }
}
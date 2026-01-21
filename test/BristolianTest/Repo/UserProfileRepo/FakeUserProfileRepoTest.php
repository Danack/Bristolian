<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserProfileRepo;

use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;

/**
 * @group standard_repo
 */
class FakeUserProfileRepoTest extends UserProfileRepoTest
{
    public function getTestInstance(): UserProfileRepo
    {
        return new FakeUserProfileRepo();
    }
}

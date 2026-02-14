<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserProfileRepo;

use Bristolian\Repo\UserProfileRepo\FakeUserProfileRepo;
use Bristolian\Repo\UserProfileRepo\UserProfileRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeUserProfileRepoFixture extends UserProfileRepoFixture
{
    public function getTestInstance(): UserProfileRepo
    {
        return new FakeUserProfileRepo();
    }
}

<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ApiTokenRepo;

use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
use Bristolian\Repo\ApiTokenRepo\FakeApiTokenRepo;

/**
 * @group standard_repo
 */
class FakeApiTokenRepoTest extends ApiTokenRepoTest
{
    /**
     * @return ApiTokenRepo
     */
    public function getTestInstance(): ApiTokenRepo
    {
        return new FakeApiTokenRepo([]);
    }
}

<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeTinnedFishProductRepoTest extends TinnedFishProductRepoFixture
{
    /**
     * @return TinnedFishProductRepo
     */
    public function getTestInstance(): TinnedFishProductRepo
    {
        return new FakeTinnedFishProductRepo([]);
    }
}

<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;

/**
 * @group standard_repo
 */
class FakeTinnedFishProductRepoTest extends TinnedFishProductRepoTest
{
    /**
     * @return TinnedFishProductRepo
     */
    public function getTestInstance(): TinnedFishProductRepo
    {
        return new FakeTinnedFishProductRepo([]);
    }
}

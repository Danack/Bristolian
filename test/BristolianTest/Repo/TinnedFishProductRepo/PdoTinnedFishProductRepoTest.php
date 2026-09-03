<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;

/**
 * @group db
 * @coversNothing
 */
class PdoTinnedFishProductRepoTest extends TinnedFishProductRepoFixture
{
    public function getTestInstance(): TinnedFishProductRepo
    {
        return $this->injector->make(PdoTinnedFishProductRepo::class);
    }
}

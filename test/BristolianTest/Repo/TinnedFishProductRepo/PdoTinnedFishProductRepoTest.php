<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * @group db
 */
#[CoversNothing]
class PdoTinnedFishProductRepoTest extends TinnedFishProductRepoFixture
{
    public function getTestInstance(): TinnedFishProductRepo
    {
        return $this->injector->make(PdoTinnedFishProductRepo::class);
    }
}

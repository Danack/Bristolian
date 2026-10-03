<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group db
 */
#[CoversClass(PdoTinnedFishProductRepo::class)]
class PdoTinnedFishProductRepoTest extends TinnedFishProductRepoFixture
{
    public function getTestInstance(): TinnedFishProductRepo
    {
        return $this->injector->make(PdoTinnedFishProductRepo::class);
    }
}

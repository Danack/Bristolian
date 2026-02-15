<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TagRepo;

use Bristolian\Repo\TagRepo\TagRepo;
use Bristolian\Repo\TagRepo\PdoTagRepo;

/**
 * @group db
 * @coversNothing
 */
class PdoTagRepoTest extends TagRepoFixture
{
    public function getTestInstance(): TagRepo
    {
        return $this->injector->make(PdoTagRepo::class);
    }
}

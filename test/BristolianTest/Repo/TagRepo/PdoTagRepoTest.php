<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TagRepo;

use Bristolian\Repo\TagRepo\TagRepo;
use Bristolian\Repo\TagRepo\PdoTagRepo;

/**
 * @group db
 */
class PdoTagRepoTest extends TagRepoTest
{
    public function getTestInstance(): TagRepo
    {
        return $this->injector->make(PdoTagRepo::class);
    }
}

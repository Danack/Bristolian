<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserRepo;

use Bristolian\Repo\UserRepo\PdoUserRepo;
use Bristolian\Repo\UserRepo\UserRepo;

/**
 * @group db
 */
class PdoUserRepoFixture extends UserRepoFixture
{
    public function getTestInstance(): UserRepo
    {
        return $this->injector->make(PdoUserRepo::class);
    }
}

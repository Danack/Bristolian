<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ApiTokenRepo;

use Bristolian\Repo\ApiTokenRepo\ApiTokenRepo;
use Bristolian\Repo\ApiTokenRepo\PdoApiTokenRepo;

/**
 * @group db
 * @coversNothing
 */
class PdoApiTokenRepoTest extends ApiTokenRepoFixture
{
    /**
     * @return ApiTokenRepo
     */
    public function getTestInstance(): ApiTokenRepo
    {
        return $this->injector->make(PdoApiTokenRepo::class);
    }
}

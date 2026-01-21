<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\BccTroRepo;

use Bristolian\Repo\BccTroRepo\BccTroRepo;
use Bristolian\Repo\BccTroRepo\PdoBccTroRepo;

/**
 * @group db
 */
class PdoBccTroRepoTest extends BccTroRepoTest
{
    public function getTestInstance(): BccTroRepo
    {
        return $this->injector->make(PdoBccTroRepo::class);
    }
}

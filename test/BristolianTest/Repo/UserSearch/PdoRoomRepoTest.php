<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use Bristolian\Repo\RoomRepo\PdoRoomRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;

/**
 * @group db
 */
class PdoRoomRepoTest extends RoomRepoTest
{
    public function getTestInstance(): RoomRepo
    {
        return $this->injector->make(PdoRoomRepo::class);
    }
}

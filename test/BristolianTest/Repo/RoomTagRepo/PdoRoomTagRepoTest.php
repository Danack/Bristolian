<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomTagRepo;

use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomTagRepoTest extends RoomTagRepoFixture
{
    public function getTestInstance(): RoomTagRepo
    {
        return $this->injector->make(PdoRoomTagRepo::class);
    }
}

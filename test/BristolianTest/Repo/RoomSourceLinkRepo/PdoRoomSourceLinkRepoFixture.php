<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomSourceLinkRepo;

use Bristolian\Repo\RoomSourceLinkRepo\PdoRoomSourceLinkRepo;
use Bristolian\Repo\RoomSourceLinkRepo\RoomSourceLinkRepo;

/**
 * @group db
 */
class PdoRoomSourceLinkRepoFixture extends RoomSourceLinkRepoFixture
{
    public function getTestInstance(): RoomSourceLinkRepo
    {
        return $this->injector->make(PdoRoomSourceLinkRepo::class);
    }
}

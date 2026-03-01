<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkTagRepo;

use Bristolian\Repo\RoomLinkTagRepo\FakeRoomLinkTagRepo;
use Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo;

/**
 * @coversNothing
 */
class FakeRoomLinkTagRepoTest extends RoomLinkTagRepoFixture
{
    public function getTestInstance(): RoomLinkTagRepo
    {
        return new FakeRoomLinkTagRepo();
    }
}

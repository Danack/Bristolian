<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileTagRepo;

use Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo;
use Bristolian\Repo\RoomFileTagRepo\RoomFileTagRepo;

/**
 * @coversNothing
 */
class FakeRoomFileTagRepoTest extends RoomFileTagRepoFixture
{
    public function getTestInstance(): RoomFileTagRepo
    {
        return new FakeRoomFileTagRepo();
    }
}

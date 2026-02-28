<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomTagRepo;

use Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeRoomTagRepoTest extends RoomTagRepoFixture
{
    public function getTestInstance(): RoomTagRepo
    {
        return new FakeRoomTagRepo();
    }
}

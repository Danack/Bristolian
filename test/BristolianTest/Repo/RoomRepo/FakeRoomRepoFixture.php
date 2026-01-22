<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;

/**
 * @group standard_repo
 */
class FakeRoomRepoFixture extends RoomRepoFixture
{
    public function getTestInstance(): RoomRepo
    {
        return new FakeRoomRepo();
    }
}
<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomRepo;

use Bristolian\Repo\RoomRepo\FakeRoomRepo;
use Bristolian\Repo\RoomRepo\RoomRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeRoomRepoFixture extends RoomRepoFixture
{
    public function getTestInstance(): RoomRepo
    {
        return new FakeRoomRepo();
    }

    protected function getValidUserId(): string
    {
        return 'user_123';
    }
}

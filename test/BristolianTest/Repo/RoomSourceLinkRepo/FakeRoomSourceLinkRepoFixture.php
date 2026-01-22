<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomSourceLinkRepo;

use Bristolian\Repo\RoomSourceLinkRepo\FakeRoomSourceLinkRepo;
use Bristolian\Repo\RoomSourceLinkRepo\RoomSourceLinkRepo;

/**
 * @group standard_repo
 */
class FakeRoomSourceLinkRepoFixture extends RoomSourceLinkRepoFixture
{
    public function getTestInstance(): RoomSourceLinkRepo
    {
        return new FakeRoomSourceLinkRepo();
    }
}

<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileObjectInfoRepo;

use Bristolian\Repo\RoomFileObjectInfoRepo\FakeRoomFileObjectInfoRepo;
use Bristolian\Repo\RoomFileObjectInfoRepo\RoomFileObjectInfoRepo;

/**
 * @group standard_repo
 */
class FakeRoomFileObjectInfoRepoTest extends RoomFileObjectInfoRepoTest
{
    /**
     * @return RoomFileObjectInfoRepo
     */
    public function getTestInstance(): RoomFileObjectInfoRepo
    {
        return new FakeRoomFileObjectInfoRepo();
    }
}

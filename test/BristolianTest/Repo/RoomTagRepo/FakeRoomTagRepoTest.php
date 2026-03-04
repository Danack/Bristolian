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
    private const FAKE_ROOM_ID = 'test-room-id-123';

    public function getTestInstance(): RoomTagRepo
    {
        return new FakeRoomTagRepo();
    }

    protected function getTestRoomId(): string
    {
        return self::FAKE_ROOM_ID;
    }
}

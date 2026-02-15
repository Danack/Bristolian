<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomSourceLinkRepo;

use Bristolian\Repo\RoomSourceLinkRepo\FakeRoomSourceLinkRepo;
use Bristolian\Repo\RoomSourceLinkRepo\RoomSourceLinkRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeRoomSourceLinkRepoTest extends RoomSourceLinkRepoFixture
{
    public function getTestInstance(): RoomSourceLinkRepo
    {
        return new FakeRoomSourceLinkRepo();
    }

    protected function getValidUserId(): string
    {
        return 'user-123';
    }

    protected function getValidRoomId(): string
    {
        return 'room-456';
    }

    protected function getValidFileId(): string
    {
        return 'file-789';
    }

    protected function getValidRoomId2(): string
    {
        return 'room-789';
    }

    protected function getValidFileId2(): string
    {
        return 'file-999';
    }
}

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

    /**
     * When a room source link references a missing source link, getSourceLinksForRoom skips it (defensive path).
     *
     * @covers \Bristolian\Repo\RoomSourceLinkRepo\FakeRoomSourceLinkRepo::getSourceLinksForRoom
     */
    public function test_getSourceLinksForRoom_skips_room_source_link_when_source_link_missing(): void
    {
        $room_id = 'room-1';
        $orphanRoomSourceLink = [
            'id' => 'rsl-1',
            'room_id' => $room_id,
            'sourcelink_id' => 'nonexistent-sourcelink-id',
            'title' => 'Orphan title',
        ];
        $repo = new FakeRoomSourceLinkRepo(
            ['rsl-1' => $orphanRoomSourceLink],
            []
        );
        $results = $repo->getSourceLinksForRoom($room_id);
        $this->assertSame([], $results);
    }
}

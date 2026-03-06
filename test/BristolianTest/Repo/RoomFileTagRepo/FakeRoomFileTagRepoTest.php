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

    /**
     * @covers \Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo::getTagIdsForRoomFile
     * @covers \Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo::setTagsForRoomFile
     */
    public function test_fake_setTags_and_getTagIds_roundtrip(): void
    {
        $repo = new FakeRoomFileTagRepo();
        $repo->setTagsForRoomFile('room-1', 'file-1', ['tag-a', 'tag-b']);
        $this->assertEquals(['tag-a', 'tag-b'], $repo->getTagIdsForRoomFile('room-1', 'file-1'));
    }
}

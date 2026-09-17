<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomFileTagRepo;

use Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo;
use Bristolian\Repo\RoomFileTagRepo\RoomFileTagRepo;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo::class, 'getTagIdsForRoomFile')]
#[CoversMethod(\Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo::class, 'key')]
#[CoversMethod(\Bristolian\Repo\RoomFileTagRepo\FakeRoomFileTagRepo::class, 'setTagsForRoomFile')]

class FakeRoomFileTagRepoTest extends RoomFileTagRepoFixture
{
    public function getTestInstance(): RoomFileTagRepo
    {
        return new FakeRoomFileTagRepo();
    }

    public function test_fake_setTags_and_getTagIds_roundtrip(): void
    {
        $repo = new FakeRoomFileTagRepo();
        $repo->setTagsForRoomFile('room-1', 'file-1', ['tag-a', 'tag-b']);
        $this->assertEquals(['tag-a', 'tag-b'], $repo->getTagIdsForRoomFile('room-1', 'file-1'));
    }

    public function test_different_room_file_pairs_are_isolated(): void
    {
        $repo = new FakeRoomFileTagRepo();
        $repo->setTagsForRoomFile('room-1', 'file-1', ['tag-a']);
        $repo->setTagsForRoomFile('room-1', 'file-2', ['tag-b']);
        $repo->setTagsForRoomFile('room-2', 'file-1', ['tag-c']);

        $this->assertSame(['tag-a'], $repo->getTagIdsForRoomFile('room-1', 'file-1'));
        $this->assertSame(['tag-b'], $repo->getTagIdsForRoomFile('room-1', 'file-2'));
        $this->assertSame(['tag-c'], $repo->getTagIdsForRoomFile('room-2', 'file-1'));
    }

    public function test_setTagsForRoomFile_with_empty_array_clears_tags(): void
    {
        $repo = new FakeRoomFileTagRepo();
        $repo->setTagsForRoomFile('room-1', 'file-1', ['tag-a', 'tag-b']);
        $this->assertSame(['tag-a', 'tag-b'], $repo->getTagIdsForRoomFile('room-1', 'file-1'));

        $repo->setTagsForRoomFile('room-1', 'file-1', []);
        $this->assertSame([], $repo->getTagIdsForRoomFile('room-1', 'file-1'));
    }

    public function test_setTagsForRoomFile_reindexes_associative_array(): void
    {
        $repo = new FakeRoomFileTagRepo();
        $repo->setTagsForRoomFile('room-1', 'file-1', [2 => 'tag-a', 5 => 'tag-b']);
        $this->assertSame(['tag-a', 'tag-b'], $repo->getTagIdsForRoomFile('room-1', 'file-1'));
    }
}

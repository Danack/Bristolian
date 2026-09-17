<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkTagRepo;

use Bristolian\Repo\RoomLinkTagRepo\FakeRoomLinkTagRepo;
use Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Repo\RoomLinkTagRepo\FakeRoomLinkTagRepo::class, 'getTagIdsForRoomLink')]
#[CoversMethod(\Bristolian\Repo\RoomLinkTagRepo\FakeRoomLinkTagRepo::class, 'setTagsForRoomLink')]

class FakeRoomLinkTagRepoTest extends RoomLinkTagRepoFixture
{
    public function getTestInstance(): RoomLinkTagRepo
    {
        return new FakeRoomLinkTagRepo();
    }

    public function test_fake_setTags_and_getTagIds_roundtrip(): void
    {
        $repo = new FakeRoomLinkTagRepo();
        $repo->setTagsForRoomLink('link-1', ['tag-a', 'tag-b']);
        $this->assertEquals(['tag-a', 'tag-b'], $repo->getTagIdsForRoomLink('link-1'));
    }
}

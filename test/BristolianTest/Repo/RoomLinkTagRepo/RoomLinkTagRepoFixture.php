<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkTagRepo;

use Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomLinkTagRepo implementations.
 *
 * @coversNothing
 */
abstract class RoomLinkTagRepoFixture extends BaseTestCase
{
    abstract public function getTestInstance(): RoomLinkTagRepo;

    /**
     * @covers \Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo::getTagIdsForRoomLink
     */
    public function test_getTagIdsForRoomLink_returns_empty_initially(): void
    {
        $repo = $this->getTestInstance();
        $ids = $repo->getTagIdsForRoomLink('link-1');
        $this->assertSame([], $ids);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo::getTagIdsForRoomLink
     * @covers \Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo::setTagsForRoomLink
     */
    public function test_setTagsForRoomLink_and_getTagIdsForRoomLink_roundtrip(): void
    {
        $repo = $this->getTestInstance();
        $link_id = 'link-1';
        $tag_ids = ['tag-a', 'tag-b'];
        $repo->setTagsForRoomLink($link_id, $tag_ids);
        $this->assertEquals($tag_ids, $repo->getTagIdsForRoomLink($link_id));
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkTagRepo\RoomLinkTagRepo::setTagsForRoomLink
     */
    public function test_setTagsForRoomLink_replaces_existing(): void
    {
        $repo = $this->getTestInstance();
        $link_id = 'link-1';
        $repo->setTagsForRoomLink($link_id, ['tag-1']);
        $repo->setTagsForRoomLink($link_id, ['tag-2', 'tag-3']);
        $this->assertEquals(['tag-2', 'tag-3'], $repo->getTagIdsForRoomLink($link_id));
    }
}

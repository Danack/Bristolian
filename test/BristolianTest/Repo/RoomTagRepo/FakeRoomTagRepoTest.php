<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomTagRepo;

use Bristolian\Exception\TooManyRoomTagsException;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use VarMap\ArrayVarMap;

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

    /**
     * @covers \Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo::getTagsForRoom
     * @covers \Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo::createTag
     */
    public function test_getTagsForRoom_and_createTag(): void
    {
        $repo = new FakeRoomTagRepo();

        $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-a',
            'description' => 'Description A',
        ]));

        $tag = $repo->createTag(self::FAKE_ROOM_ID, $tagParam);
        $this->assertInstanceOf(RoomTag::class, $tag);
        $this->assertSame('tag-a', $tag->text);

        $tags = $repo->getTagsForRoom(self::FAKE_ROOM_ID);
        $this->assertCount(1, $tags);
        $this->assertSame('tag-a', $tags[0]->text);
    }

    /**
     * @covers \Bristolian\Repo\RoomTagRepo\FakeRoomTagRepo::createTag
     */
    public function test_createTag_throws_when_limit_reached(): void
    {
        $repo = new FakeRoomTagRepo();

        for ($i = 0; $i < RoomTagRepo::MAX_TAGS_PER_ROOM; $i++) {
            $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
                'text' => "tag-$i",
                'description' => "Tag $i",
            ]));
            $repo->createTag(self::FAKE_ROOM_ID, $tagParam);
        }

        $tagParamOver = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-over',
            'description' => 'Over limit',
        ]));

        $this->expectException(TooManyRoomTagsException::class);
        $repo->createTag(self::FAKE_ROOM_ID, $tagParamOver);
    }
}

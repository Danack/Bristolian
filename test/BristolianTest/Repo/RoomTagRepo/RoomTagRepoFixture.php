<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomTagRepo;

use Bristolian\Exception\TooManyRoomTagsException;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for RoomTagRepo implementations.
 *
 * @coversNothing
 */
abstract class RoomTagRepoFixture extends BaseTestCase
{
    abstract public function getTestInstance(): RoomTagRepo;

    /**
     * Room id to use for tests (must exist in DB for PdoRoomTagRepo).
     */
    abstract protected function getTestRoomId(): string;

    /**
     * @covers \Bristolian\Repo\RoomTagRepo\RoomTagRepo::getTagsForRoom
     */
    public function test_getTagsForRoom_returns_array(): void
    {
        $repo = $this->getTestInstance();

        $tags = $repo->getTagsForRoom($this->getTestRoomId());

        foreach ($tags as $tag) {
            $this->assertInstanceOf(RoomTag::class, $tag);
        }
    }

    /**
     * @covers \Bristolian\Repo\RoomTagRepo\RoomTagRepo::getTagsForRoom
     * @covers \Bristolian\Repo\RoomTagRepo\RoomTagRepo::createTag
     */
    public function test_getTagsForRoom_returns_tags_created_for_that_room(): void
    {
        $repo = $this->getTestInstance();

        $tagParam1 = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-1',
            'description' => 'First tag',
        ]));
        $tagParam2 = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-2',
            'description' => 'Second tag',
        ]));

        $repo->createTag($this->getTestRoomId(), $tagParam1);
        $repo->createTag($this->getTestRoomId(), $tagParam2);

        $tags = $repo->getTagsForRoom($this->getTestRoomId());

        $this->assertGreaterThanOrEqual(2, count($tags));
        $this->assertContainsOnlyInstancesOf(RoomTag::class, $tags);

        $tagTexts = array_map(fn(RoomTag $t) => $t->text, $tags);
        $this->assertContains('tag-1', $tagTexts);
        $this->assertContains('tag-2', $tagTexts);
    }

    /**
     *
     * @group production_test
     * @covers \Bristolian\Repo\RoomTagRepo\RoomTagRepo::createTag
     * @covers \Bristolian\Exception\TooManyRoomTagsException
     */
    public function test_createTag_throws_when_limit_reached(): void
    {
        $repo = $this->getTestInstance();

        for ($i = 0; $i < RoomTagRepo::MAX_TAGS_PER_ROOM; $i++) {
            $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
                'text' => "tag-$i",
                'description' => "Tag $i",
            ]));
            $repo->createTag($this->getTestRoomId(), $tagParam);
        }

        $tagParamOver = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'tag-over-limit',
            'description' => 'Should fail',
        ]));

        $this->expectException(TooManyRoomTagsException::class);
        $repo->createTag($this->getTestRoomId(), $tagParamOver);
    }
}

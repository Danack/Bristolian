<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomTagRepo;

use Bristolian\Exception\TooManyRoomTagsException;
use Bristolian\Model\Generated\RoomTag;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use Bristolian\Repo\RoomTagRepo\RoomTagRepo;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomTagRepoTest extends RoomTagRepoFixture
{
    use HasTestWorld;

    private ?string $testRoomId = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        $userId = $this->standardTestData()->getTestingUserId();
        $room = $this->world()->roomRepo()->createRoom($userId, 'room' . time() . '_' . random_int(100, 999), 'Room tag tests');
        $this->testRoomId = $room->id;
    }

    public function getTestInstance(): RoomTagRepo
    {
        return $this->injector->make(PdoRoomTagRepo::class);
    }

    protected function getTestRoomId(): string
    {
        assert($this->testRoomId !== null);
        return $this->testRoomId;
    }

    /**
     * @covers \Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo::__construct
     * @covers \Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo::createTag
     */
    public function test_pdo_createTag_persists_and_returns_tag(): void
    {
        $repo = $this->getTestInstance();
        $roomId = $this->getTestRoomId();

        $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'pdo-tag-' . create_test_uniqid(),
            'description' => 'Pdo tag description',
        ]));

        $tag = $repo->createTag($roomId, $tagParam);

        $this->assertInstanceOf(RoomTag::class, $tag);
        $this->assertSame($tagParam->text, $tag->text);
        $this->assertSame($roomId, $tag->room_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo::getTagsForRoom
     */
    public function test_pdo_getTagsForRoom_returns_created_tags(): void
    {
        $repo = $this->getTestInstance();
        $roomId = $this->getTestRoomId();

        $text = 'pdo-get-tags-' . create_test_uniqid();
        $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => $text,
            'description' => 'Description',
        ]));

        $repo->createTag($roomId, $tagParam);
        $tags = $repo->getTagsForRoom($roomId);

        $this->assertNotEmpty($tags);
        $texts = array_map(fn(RoomTag $t) => $t->text, $tags);
        $this->assertContains($text, $texts);
    }

    /**
     * @covers \Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo::createTag
     */
    public function test_pdo_createTag_throws_when_max_tags_reached(): void
    {
        $repo = $this->getTestInstance();
        $roomId = $this->getTestRoomId();

        for ($i = 0; $i < RoomTagRepo::MAX_TAGS_PER_ROOM; $i++) {
            $tagParam = TagParams::createFromVarMap(new ArrayVarMap([
                'text' => "max-$i-" . create_test_uniqid(),
                'description' => "Tag $i",
            ]));
            $repo->createTag($roomId, $tagParam);
        }

        $overParam = TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'over-limit',
            'description' => 'Over',
        ]));

        $this->expectException(TooManyRoomTagsException::class);
        $repo->createTag($roomId, $overParam);
    }
}

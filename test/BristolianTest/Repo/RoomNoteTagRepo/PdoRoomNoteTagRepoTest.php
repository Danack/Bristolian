<?php

declare(strict_types=1);

namespace BristolianTest\Repo\RoomNoteTagRepo;

use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomNoteRepo\PdoRoomNoteRepo;
use Bristolian\Repo\RoomNoteTagRepo\PdoRoomNoteTagRepo;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;

/**
 * @group db
 * @coversNothing
 */
class PdoRoomNoteTagRepoTest extends BaseTestCase
{
    use HasTestWorld;
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\RoomNoteTagRepo\PdoRoomNoteTagRepo
     */
    public function test_setTagsForRoomNote_and_getTagIdsForRoomNote_roundtrip(): void
    {
        $this->ensureStandardSetup();
        [$room, $user] = $this->createTestUserAndRoom();
        $noteRepo = $this->injector->make(PdoRoomNoteRepo::class);
        $noteId = $noteRepo->create($user->getUserId(), $room->id, 'Tagged', 'body', null);

        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag1 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'notetag-a-' . create_test_uniqid(),
            'description' => 'Tag A',
        ])));
        $tag2 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'notetag-b-' . create_test_uniqid(),
            'description' => 'Tag B',
        ])));

        $repo = $this->injector->make(PdoRoomNoteTagRepo::class);
        $this->assertSame([], $repo->getTagIdsForRoomNote($noteId));
        $repo->setTagsForRoomNote($noteId, [$tag1->tag_id, $tag2->tag_id]);
        $ids = $repo->getTagIdsForRoomNote($noteId);
        sort($ids);
        $expected = [$tag1->tag_id, $tag2->tag_id];
        sort($expected);
        $this->assertSame($expected, $ids);
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\Repo\RoomNoteTagRepo;

use Bristolian\Repo\RoomNoteTagRepo\FakeRoomNoteTagRepo;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FakeRoomNoteTagRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Repo\RoomNoteTagRepo\FakeRoomNoteTagRepo
     */
    public function test_set_and_get_roundtrip(): void
    {
        $repo = new FakeRoomNoteTagRepo();
        $this->assertSame([], $repo->getTagIdsForRoomNote('note-1'));
        $repo->setTagsForRoomNote('note-1', ['tag-a', 'tag-b']);
        $this->assertSame(['tag-a', 'tag-b'], $repo->getTagIdsForRoomNote('note-1'));
        $repo->setTagsForRoomNote('note-1', ['tag-c']);
        $this->assertSame(['tag-c'], $repo->getTagIdsForRoomNote('note-1'));
    }
}

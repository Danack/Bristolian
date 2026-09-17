<?php

declare(strict_types=1);

namespace BristolianTest\Repo\RoomNoteRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Repo\RoomNoteRepo\FakeRoomNoteRepo;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Repo\RoomNoteRepo\FakeRoomNoteRepo::class)]

class FakeRoomNoteRepoTest extends BaseTestCase
{
    public function test_create_get_update_delete(): void
    {
        $repo = new FakeRoomNoteRepo();
        $id = $repo->create('user-1', 'room-1', 'Title', 'Body', null);

        $note = $repo->getNote('room-1', $id);
        $this->assertSame('Title', $note->title);
        $this->assertSame('Body', $note->markdown);

        $notes = $repo->getNotesForRoom('room-1', RoomContentSearchParams::default());
        $this->assertCount(1, $notes);

        $repo->update('room-1', $id, 'New title', 'New body', null);
        $updated = $repo->getNote('room-1', $id);
        $this->assertSame('New title', $updated->title);
        $this->assertSame('New body', $updated->markdown);

        $repo->delete('room-1', $id);
        $this->expectException(ContentNotFoundException::class);
        $repo->getNote('room-1', $id);
    }

    public function test_search_by_title_and_markdown(): void
    {
        $repo = new FakeRoomNoteRepo();
        $repo->create('user-1', 'room-1', 'Alpha note', 'contains zebra', null);
        $repo->create('user-1', 'room-1', 'Beta note', 'contains llama', null);

        $byTitle = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['title' => 'Alpha'])
        ));
        $this->assertCount(1, $byTitle);
        $this->assertSame('Alpha note', $byTitle[0]->title);

        $byMarkdown = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['description' => 'llama'])
        ));
        $this->assertCount(1, $byMarkdown);
        $this->assertSame('Beta note', $byMarkdown[0]->title);
    }
}

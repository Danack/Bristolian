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

    public function test_getNotesForRoom_filters_by_created_at_range(): void
    {
        $repo = new FakeRoomNoteRepo();
        $repo->create('user-1', 'room-1', 'Dated note', 'body', null);

        $included = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['created_at_after' => '2000-01-01'])
        ));
        $this->assertCount(1, $included);

        $excluded = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['created_at_after' => '2020-01-01'])
        ));
        $this->assertCount(0, $excluded);

        $excludedBefore = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['created_at_before' => '2000-01-01'])
        ));
        $this->assertCount(0, $excludedBefore);
    }

    public function test_getNotesForRoom_filters_by_document_timestamp(): void
    {
        $repo = new FakeRoomNoteRepo();
        $repo->create(
            'user-1',
            'room-1',
            'Doc note',
            'body',
            new \DateTimeImmutable('2015-06-01')
        );

        $included = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['document_timestamp_after' => '2015-01-01'])
        ));
        $this->assertCount(1, $included);

        $excluded = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['document_timestamp_before' => '2015-01-01'])
        ));
        $this->assertCount(0, $excluded);

        $excludedAfter = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['document_timestamp_after' => '2016-01-01'])
        ));
        $this->assertCount(0, $excludedAfter);
    }

    public function test_getNotesForRoom_skips_document_timestamp_filter_when_note_has_no_timestamp(): void
    {
        $repo = new FakeRoomNoteRepo();
        $repo->create('user-1', 'room-1', 'No doc date', 'body', null);

        $results = $repo->getNotesForRoom('room-1', RoomContentSearchParams::createFromVarMap(
            new \VarMap\ArrayVarMap(['document_timestamp_after' => '2015-01-01'])
        ));
        $this->assertCount(1, $results);
    }

    public function test_update_skips_non_matching_notes_then_updates_target(): void
    {
        $repo = new FakeRoomNoteRepo();
        $firstId = $repo->create('user-1', 'room-1', 'First', 'body1', null);
        $secondId = $repo->create('user-1', 'room-1', 'Second', 'body2', null);

        $repo->update('room-1', $secondId, 'Second updated', 'body2-updated', null);

        $this->assertSame('First', $repo->getNote('room-1', $firstId)->title);
        $this->assertSame('Second updated', $repo->getNote('room-1', $secondId)->title);
    }

    public function test_update_throws_when_note_missing(): void
    {
        $repo = new FakeRoomNoteRepo();

        $this->expectException(ContentNotFoundException::class);
        $repo->update('room-1', 'missing-id', 't', 'm', null);
    }

    public function test_delete_throws_when_note_missing(): void
    {
        $repo = new FakeRoomNoteRepo();

        $this->expectException(ContentNotFoundException::class);
        $repo->delete('room-1', 'missing-id');
    }
}

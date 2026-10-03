<?php

declare(strict_types=1);

namespace BristolianTest\Repo\RoomNoteRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Parameters\RoomContentSearchParams;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomNoteRepo\PdoRoomNoteRepo;
use Bristolian\Repo\RoomNoteTagRepo\PdoRoomNoteTagRepo;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @group db
 */

#[CoversClass(\Bristolian\Repo\RoomNoteRepo\PdoRoomNoteRepo::class)]

class PdoRoomNoteRepoTest extends BaseTestCase
{
    use HasTestWorld;
    use TestPlaceholders;

    public function test_create_get_update_delete_and_search(): void
    {
        $this->ensureStandardSetup();
        [$room, $user] = $this->createTestUserAndRoom();
        $otherRoom = $this->world()->roomRepo()->createRoom(
            $user->getUserId(),
            'N' . time() . '_' . random_int(100, 999),
            'Other notes room'
        );

        $repo = $this->injector->make(PdoRoomNoteRepo::class);
        $documentTimestamp = new DateTimeImmutable('2021-05-06 07:08:09');
        $id = $repo->create(
            $user->getUserId(),
            $room->id,
            'Meeting agenda',
            'Discuss **parking**',
            $documentTimestamp
        );

        $note = $repo->getNote($room->id, $id);
        $this->assertSame('Meeting agenda', $note->title);
        $this->assertSame('Discuss **parking**', $note->markdown);
        $this->assertSame($room->id, $note->room_id);

        $listed = $repo->getNotesForRoom($room->id, RoomContentSearchParams::default());
        $this->assertCount(1, $listed);
        $this->assertSame($id, $listed[0]->id);

        $byTitle = $repo->getNotesForRoom($room->id, RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['title' => 'agenda'])
        ));
        $this->assertCount(1, $byTitle);

        $byMarkdown = $repo->getNotesForRoom($room->id, RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['description' => 'parking'])
        ));
        $this->assertCount(1, $byMarkdown);

        $byDate = $repo->getNotesForRoom($room->id, RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['document_timestamp_after' => '2021-05-01'])
        ));
        $this->assertCount(1, $byDate);

        $tagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag = $tagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'note-tag-' . create_test_uniqid(),
            'description' => 'Note tag',
        ])));
        $noteTagRepo = $this->injector->make(PdoRoomNoteTagRepo::class);
        $noteTagRepo->setTagsForRoomNote($id, [$tag->tag_id]);

        $byTag = $repo->getNotesForRoom($room->id, RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['tag_ids' => $tag->tag_id])
        ));
        $this->assertCount(1, $byTag);

        $repo->update($room->id, $id, 'Updated agenda', 'Updated **parking**', null);
        $updated = $repo->getNote($room->id, $id);
        $this->assertSame('Updated agenda', $updated->title);
        $this->assertSame('Updated **parking**', $updated->markdown);
        $this->assertNull($updated->document_timestamp);

        $this->expectException(ContentNotFoundException::class);
        $repo->getNote($otherRoom->id, $id);
    }

    public function test_delete_removes_note(): void
    {
        $this->ensureStandardSetup();
        [$room, $user] = $this->createTestUserAndRoom();
        $repo = $this->injector->make(PdoRoomNoteRepo::class);
        $id = $repo->create($user->getUserId(), $room->id, 'To delete', 'gone', null);
        $repo->delete($room->id, $id);

        $this->expectException(ContentNotFoundException::class);
        $repo->getNote($room->id, $id);
    }

    public function test_update_throws_when_missing(): void
    {
        $this->ensureStandardSetup();
        [$room] = $this->createTestUserAndRoom();
        $repo = $this->injector->make(PdoRoomNoteRepo::class);

        $this->expectException(ContentNotFoundException::class);
        $repo->update($room->id, '00000000-0000-0000-0000-000000000000', 'x', 'y', null);
    }

    public function test_getNotesForRoom_filters_by_created_at_and_document_timestamp_before(): void
    {
        $this->ensureStandardSetup();
        [$room, $user] = $this->createTestUserAndRoom();
        $repo = $this->injector->make(PdoRoomNoteRepo::class);
        $repo->create(
            $user->getUserId(),
            $room->id,
            'Timestamped note',
            'body',
            new DateTimeImmutable('2021-06-15 12:00:00')
        );

        $afterCreated = $repo->getNotesForRoom($room->id, RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['created_at_after' => '2020-01-01'])
        ));
        $this->assertCount(1, $afterCreated);

        $beforeCreated = $repo->getNotesForRoom($room->id, RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['created_at_before' => '2099-01-01'])
        ));
        $this->assertCount(1, $beforeCreated);

        $beforeDocument = $repo->getNotesForRoom($room->id, RoomContentSearchParams::createFromVarMap(
            new ArrayVarMap(['document_timestamp_before' => '2021-07-01'])
        ));
        $this->assertCount(1, $beforeDocument);
    }

    public function test_delete_throws_when_missing(): void
    {
        $this->ensureStandardSetup();
        [$room] = $this->createTestUserAndRoom();
        $repo = $this->injector->make(PdoRoomNoteRepo::class);

        $this->expectException(ContentNotFoundException::class);
        $repo->delete($room->id, '00000000-0000-0000-0000-000000000000');
    }
}

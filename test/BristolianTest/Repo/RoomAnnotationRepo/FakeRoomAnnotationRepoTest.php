<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationRepo;

use Bristolian\Exception\ContentNotFoundException;
use Bristolian\Parameters\AnnotationParam;
use Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo;
use Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo;
use VarMap\ArrayVarMap;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeRoomAnnotationRepoTest extends RoomAnnotationRepoFixture
{
    public function getTestInstance(): RoomAnnotationRepo
    {
        return new FakeRoomAnnotationRepo();
    }

    protected function getValidUserId(): string
    {
        return 'user-123';
    }

    protected function getValidRoomId(): string
    {
        return 'room-456';
    }

    protected function getValidFileId(): string
    {
        return 'file-789';
    }

    protected function getValidRoomId2(): string
    {
        return 'room-789';
    }

    protected function getValidFileId2(): string
    {
        return 'file-999';
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::__construct
     */
    public function test_fake_construct_with_no_args_returns_empty_from_getAnnotationsForRoom(): void
    {
        $repo = new FakeRoomAnnotationRepo();
        $this->assertSame([], $repo->getAnnotationsForRoom('any-room'));
    }

    /**
     * When a room annotation references a missing annotation, getAnnotationsForRoom skips it (defensive path).
     *
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoom
     */
    public function test_getAnnotationsForRoom_skips_room_annotation_when_annotation_missing(): void
    {
        $room_id = 'room-1';
        $orphanRoomAnnotation = [
            'id' => 'ra-1',
            'room_id' => $room_id,
            'annotation_id' => 'nonexistent-annotation-id',
            'title' => 'Orphan title',
        ];
        $repo = new FakeRoomAnnotationRepo(
            ['ra-1' => $orphanRoomAnnotation],
            []
        );
        $results = $repo->getAnnotationsForRoom($room_id);
        $this->assertSame([], $results);
    }

    /**
     * When a room annotation references a missing annotation, getAnnotationsForRoomAndTitle skips it.
     *
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndTitle
     */
    public function test_getAnnotationsForRoomAndTitle_skips_room_annotation_when_annotation_missing(): void
    {
        $room_id = 'room-1';
        $title = 'Orphan Title That Is Long Enough';
        $orphanRoomAnnotation = [
            'id' => 'ra-1',
            'room_id' => $room_id,
            'annotation_id' => 'nonexistent-annotation-id',
            'title' => $title,
        ];
        $repo = new FakeRoomAnnotationRepo(
            ['ra-1' => $orphanRoomAnnotation],
            []
        );
        $results = $repo->getAnnotationsForRoomAndTitle($room_id, $title);
        $this->assertSame([], $results);
    }

    /**
     * updateTitleAndText throws when the annotation row is missing even if room_annotation exists.
     *
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::updateTitleAndText
     */
    public function test_updateTitleAndText_throws_when_annotation_row_missing(): void
    {
        $room_id = 'room-1';
        $roomAnnotation = [
            'id' => 'ra-1',
            'room_id' => $room_id,
            'annotation_id' => 'missing-annotation-id',
            'title' => 'Existing Title That Is Long Enough',
        ];
        $repo = new FakeRoomAnnotationRepo(['ra-1' => $roomAnnotation], []);
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('Annotation not found in room');
        $repo->updateTitleAndText($room_id, 'ra-1', 'New Title That Is Long Enough', 'New text');
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndTitle
     */
    public function test_getAnnotationsForRoomAndTitle_returns_empty_when_room_does_not_match(): void
    {
        $repo = new FakeRoomAnnotationRepo();
        $title = 'Title That Is Long Enough For Validation ' . create_test_uniqid();
        $repo->addAnnotation(
            'user-1',
            'room-a',
            'file-1',
            AnnotationParam::createFromVarMap(new ArrayVarMap([
                'title' => $title,
                'highlights_json' => '{"highlights": []}',
                'text' => 'Body',
            ]))
        );
        $results = $repo->getAnnotationsForRoomAndTitle('room-b', $title);
        $this->assertSame([], $results);
    }
}

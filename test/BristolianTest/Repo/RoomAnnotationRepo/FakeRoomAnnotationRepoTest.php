<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationRepo;

use Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo;
use Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo;

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
}

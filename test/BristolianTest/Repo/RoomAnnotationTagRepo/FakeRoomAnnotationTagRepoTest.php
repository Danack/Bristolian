<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationTagRepo;

use Bristolian\Repo\RoomAnnotationTagRepo\FakeRoomAnnotationTagRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;

/**
 * @coversNothing
 */
class FakeRoomAnnotationTagRepoTest extends RoomAnnotationTagRepoFixture
{
    public function getTestInstance(): RoomAnnotationTagRepo
    {
        return new FakeRoomAnnotationTagRepo();
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationTagRepo\FakeRoomAnnotationTagRepo::getTagIdsForRoomAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationTagRepo\FakeRoomAnnotationTagRepo::setTagsForRoomAnnotation
     */
    public function test_fake_setTags_and_getTagIds_roundtrip(): void
    {
        $repo = new FakeRoomAnnotationTagRepo();
        $repo->setTagsForRoomAnnotation('ann-1', ['tag-a', 'tag-b']);
        $this->assertEquals(['tag-a', 'tag-b'], $repo->getTagIdsForRoomAnnotation('ann-1'));
    }
}

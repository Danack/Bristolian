<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationTagRepo;

use Bristolian\Repo\RoomAnnotationTagRepo\FakeRoomAnnotationTagRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FakeRoomAnnotationTagRepo::class)]
class FakeRoomAnnotationTagRepoTest extends RoomAnnotationTagRepoFixture
{
    public function getTestInstance(): RoomAnnotationTagRepo
    {
        return new FakeRoomAnnotationTagRepo();
    }

    public function test_fake_setTags_and_getTagIds_roundtrip(): void
    {
        $repo = new FakeRoomAnnotationTagRepo();
        $repo->setTagsForRoomAnnotation('ann-1', ['tag-a', 'tag-b']);
        $this->assertEquals(['tag-a', 'tag-b'], $repo->getTagIdsForRoomAnnotation('ann-1'));
    }
}

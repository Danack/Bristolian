<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationTagRepo;

use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for RoomAnnotationTagRepo implementations.
 *
 * @coversNothing
 */
abstract class RoomAnnotationTagRepoFixture extends BaseTestCase
{
    abstract public function getTestInstance(): RoomAnnotationTagRepo;

    /**
     * @covers \Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo::getTagIdsForRoomAnnotation
     */
    public function test_getTagIdsForRoomAnnotation_returns_empty_initially(): void
    {
        $repo = $this->getTestInstance();
        $ids = $repo->getTagIdsForRoomAnnotation('ann-1');
        $this->assertSame([], $ids);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo::getTagIdsForRoomAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo::setTagsForRoomAnnotation
     */
    public function test_setTagsForRoomAnnotation_and_getTagIdsForRoomAnnotation_roundtrip(): void
    {
        $repo = $this->getTestInstance();
        $ann_id = 'ann-1';
        $tag_ids = ['tag-a', 'tag-b'];
        $repo->setTagsForRoomAnnotation($ann_id, $tag_ids);
        $this->assertEquals($tag_ids, $repo->getTagIdsForRoomAnnotation($ann_id));
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo::setTagsForRoomAnnotation
     */
    public function test_setTagsForRoomAnnotation_replaces_existing(): void
    {
        $repo = $this->getTestInstance();
        $ann_id = 'ann-1';
        $repo->setTagsForRoomAnnotation($ann_id, ['tag-1']);
        $repo->setTagsForRoomAnnotation($ann_id, ['tag-2', 'tag-3']);
        $this->assertEquals(['tag-2', 'tag-3'], $repo->getTagIdsForRoomAnnotation($ann_id));
    }
}

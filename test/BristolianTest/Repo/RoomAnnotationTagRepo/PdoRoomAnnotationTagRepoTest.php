<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationTagRepo;

use Bristolian\Parameters\AnnotationParam;
use Bristolian\Parameters\TagParams;
use Bristolian\Repo\RoomAnnotationTagRepo\PdoRoomAnnotationTagRepo;
use Bristolian\Repo\RoomAnnotationTagRepo\RoomAnnotationTagRepo;
use Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo;
use Bristolian\Repo\RoomTagRepo\PdoRoomTagRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;
use BristolianTest\Support\HasTestWorld;
use VarMap\ArrayVarMap;
use PHPUnit\Framework\Attributes\CoversMethod;

/**
 * @group db
 */

#[CoversMethod(\Bristolian\Repo\RoomAnnotationTagRepo\PdoRoomAnnotationTagRepo::class, '__construct')]
#[CoversMethod(\Bristolian\Repo\RoomAnnotationTagRepo\PdoRoomAnnotationTagRepo::class, 'getTagIdsForRoomAnnotation')]
#[CoversMethod(\Bristolian\Repo\RoomAnnotationTagRepo\PdoRoomAnnotationTagRepo::class, 'setTagsForRoomAnnotation')]

class PdoRoomAnnotationTagRepoTest extends BaseTestCase
{
    use HasTestWorld;
    use TestPlaceholders;

    private ?string $roomAnnotationId = null;
    private ?string $tagId1 = null;
    private ?string $tagId2 = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->ensureStandardSetup();
        [$room, $user] = $this->createTestUserAndRoom();
        $fileId = $this->createTestFile($user);
        $annotationRepo = $this->injector->make(PdoRoomAnnotationRepo::class);
        $param = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Annotation for tag test',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text content',
        ]));
        $this->roomAnnotationId = $annotationRepo->addAnnotation(
            $user->getUserId(),
            $room->id,
            $fileId,
            $param
        );
        $roomTagRepo = $this->injector->make(PdoRoomTagRepo::class);
        $tag1 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'anntag-a-' . create_test_uniqid(),
            'description' => 'Tag A',
        ])));
        $tag2 = $roomTagRepo->createTag($room->id, TagParams::createFromVarMap(new ArrayVarMap([
            'text' => 'anntag-b-' . create_test_uniqid(),
            'description' => 'Tag B',
        ])));
        $this->tagId1 = $tag1->tag_id;
        $this->tagId2 = $tag2->tag_id;
    }

    private function getRepo(): RoomAnnotationTagRepo
    {
        return $this->injector->make(PdoRoomAnnotationTagRepo::class);
    }

    public function test_getTagIdsForRoomAnnotation_returns_empty_before_set(): void
    {
        $repo = $this->getRepo();
        assert($this->roomAnnotationId !== null);
        $this->assertSame([], $repo->getTagIdsForRoomAnnotation($this->roomAnnotationId));
    }

    public function test_setTagsForRoomAnnotation_and_getTagIdsForRoomAnnotation_roundtrip(): void
    {
        $repo = $this->getRepo();
        assert($this->roomAnnotationId !== null && $this->tagId1 !== null && $this->tagId2 !== null);
        $repo->setTagsForRoomAnnotation($this->roomAnnotationId, [$this->tagId1, $this->tagId2]);
        $ids = $repo->getTagIdsForRoomAnnotation($this->roomAnnotationId);
        $this->assertCount(2, $ids);
        $this->assertContains($this->tagId1, $ids);
        $this->assertContains($this->tagId2, $ids);
    }

    public function test_setTagsForRoomAnnotation_replaces_existing(): void
    {
        $repo = $this->getRepo();
        assert($this->roomAnnotationId !== null && $this->tagId1 !== null && $this->tagId2 !== null);
        $repo->setTagsForRoomAnnotation($this->roomAnnotationId, [$this->tagId1]);
        $repo->setTagsForRoomAnnotation($this->roomAnnotationId, [$this->tagId2]);
        $ids = $repo->getTagIdsForRoomAnnotation($this->roomAnnotationId);
        $this->assertCount(1, $ids);
        $this->assertSame($this->tagId2, $ids[0]);
    }
}

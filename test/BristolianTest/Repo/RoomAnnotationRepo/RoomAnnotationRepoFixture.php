<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationRepo;

use Bristolian\Model\Types\RoomAnnotationView;
use Bristolian\Parameters\AnnotationParam;
use Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for RoomAnnotationRepo implementations.
 *
 * Scenario data (user id, room id, file id) is provided by concrete tests.
 * See docs/refactoring/default_test_scenarios_and_worlds.md § Abstract repo fixtures.
 *
 * @coversNothing
 */
abstract class RoomAnnotationRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomAnnotationRepo implementation.
     *
     * @return RoomAnnotationRepo
     */
    abstract public function getTestInstance(): RoomAnnotationRepo;

    /**
     * A user id that exists in this implementation's world (for FK-safe tests).
     */
    abstract protected function getValidUserId(): string;

    /**
     * A room id that exists in this implementation's world (for FK-safe tests).
     */
    abstract protected function getValidRoomId(): string;

    /**
     * A file id that exists in this implementation's world (for FK-safe tests).
     */
    abstract protected function getValidFileId(): string;

    /**
     * A second room id (for tests that need two rooms).
     */
    abstract protected function getValidRoomId2(): string;

    /**
     * A second file id (for tests that need two files).
     */
    abstract protected function getValidFileId2(): string;

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::__construct
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_addAnnotation_returns_room_annotation_id(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Annotation Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $room_annotation_id = $repo->addAnnotation(
            $this->getValidUserId(),
            $this->getValidRoomId(),
            $this->getValidFileId(),
            $annotationParam
        );

        $this->assertNotEmpty($room_annotation_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_addAnnotation_creates_unique_ids(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Annotation Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $id1 = $repo->addAnnotation($this->getValidUserId(), $this->getValidRoomId(), $this->getValidFileId(), $annotationParam);
        $id2 = $repo->addAnnotation($this->getValidUserId(), $this->getValidRoomId(), $this->getValidFileId(), $annotationParam);

        $this->assertNotSame($id1, $id2);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoom_returns_links_for_room(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $repo->addAnnotation($this->getValidUserId(), $roomId, $this->getValidFileId(), $annotationParam);

        $links = $repo->getAnnotationsForRoom($roomId);

        $this->assertCount(1, $links);
        $this->assertContainsOnlyInstancesOf(RoomAnnotationView::class, $links);
        $this->assertSame('Test Source Link Title That Is Long Enough', $links[0]->title);
        $this->assertSame('Test text content', $links[0]->text);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoom_returns_only_links_for_specified_room(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam1 = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 1 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 1',
        ]));
        $annotationParam2 = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 2 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 2',
        ]));

        $roomId1 = $this->getValidRoomId();
        $roomId2 = $this->getValidRoomId2();
        $repo->addAnnotation($this->getValidUserId(), $roomId1, $this->getValidFileId(), $annotationParam1);
        $repo->addAnnotation($this->getValidUserId(), $roomId2, $this->getValidFileId(), $annotationParam2);

        $links = $repo->getAnnotationsForRoom($roomId1);

        $this->assertCount(1, $links);
        $this->assertSame('Link 1 Title That Is Long Enough', $links[0]->title);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoomAndFile_returns_links_matching_both_room_and_file(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId = $this->getValidFileId();
        $repo->addAnnotation($this->getValidUserId(), $roomId, $fileId, $annotationParam);

        $links = $repo->getAnnotationsForRoomAndFile($roomId, $fileId);

        $this->assertCount(1, $links);
        $this->assertContainsOnlyInstancesOf(RoomAnnotationView::class, $links);
        $this->assertSame($fileId, $links[0]->file_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoomAndFile_filters_by_both_room_and_file(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam1 = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 1 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 1',
        ]));
        $annotationParam2 = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 2 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 2',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId1 = $this->getValidFileId();
        $fileId2 = $this->getValidFileId2();
        $repo->addAnnotation($this->getValidUserId(), $roomId, $fileId1, $annotationParam1);
        $repo->addAnnotation($this->getValidUserId(), $roomId, $fileId2, $annotationParam2);

        $links = $repo->getAnnotationsForRoomAndFile($roomId, $fileId1);

        $this->assertCount(1, $links);
        $this->assertSame('Link 1 Title That Is Long Enough', $links[0]->title);
        $this->assertSame($fileId1, $links[0]->file_id);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoomAndFile_returns_empty_when_room_mismatches(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId = $this->getValidFileId();
        $repo->addAnnotation($this->getValidUserId(), $roomId, $fileId, $annotationParam);

        $links = $repo->getAnnotationsForRoomAndFile('nonexistent-room-id', $fileId);

        $this->assertEmpty($links);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoomAndFile
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoomAndFile_returns_empty_when_file_mismatches(): void
    {
        $repo = $this->getTestInstance();

        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId = $this->getValidFileId();
        $repo->addAnnotation($this->getValidUserId(), $roomId, $fileId, $annotationParam);

        $links = $repo->getAnnotationsForRoomAndFile($roomId, 'nonexistent-file-id');

        $this->assertEmpty($links);
    }
}

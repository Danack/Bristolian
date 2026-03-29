<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomAnnotationRepo;

use Bristolian\Exception\ContentNotFoundException;
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

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoomAndTitle
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndTitle
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoomAndTitle
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoomAndTitle_returns_matching_annotation(): void
    {
        $repo = $this->getTestInstance();
        $title = 'Unique Annotation Title That Is Long Enough ' . create_test_uniqid();
        $annotationParam = AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => $title,
            'highlights_json' => '{"highlights": []}',
            'text' => 'Body text one',
        ]));
        $roomId = $this->getValidRoomId();
        $repo->addAnnotation($this->getValidUserId(), $roomId, $this->getValidFileId(), $annotationParam);

        $matches = $repo->getAnnotationsForRoomAndTitle($roomId, $title);

        $this->assertCount(1, $matches);
        $this->assertSame($title, $matches[0]->title);
        $this->assertSame('Body text one', $matches[0]->text);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoomAndTitle
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoomAndTitle
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoomAndTitle
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_getAnnotationsForRoomAndTitle_filters_by_title(): void
    {
        $repo = $this->getTestInstance();
        $roomId = $this->getValidRoomId();
        $suffix = create_test_uniqid();
        $titleAlpha = 'Alpha Title That Is Long Enough ' . $suffix;
        $titleBeta = 'Beta Title That Is Long Enough ' . $suffix;
        $repo->addAnnotation($this->getValidUserId(), $roomId, $this->getValidFileId(), AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => $titleAlpha,
            'highlights_json' => '{"highlights": []}',
            'text' => 'Alpha text',
        ])));
        $repo->addAnnotation($this->getValidUserId(), $roomId, $this->getValidFileId(), AnnotationParam::createFromVarMap(new ArrayVarMap([
            'title' => $titleBeta,
            'highlights_json' => '{"highlights": []}',
            'text' => 'Beta text',
        ])));

        $matches = $repo->getAnnotationsForRoomAndTitle($roomId, $titleAlpha);

        $this->assertCount(1, $matches);
        $this->assertSame($titleAlpha, $matches[0]->title);
        $this->assertSame('Alpha text', $matches[0]->text);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::getAnnotationsForRoom
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::getAnnotationsForRoom
     */
    public function test_updateTitleAndText_updates_title_and_text(): void
    {
        $repo = $this->getTestInstance();
        $roomId = $this->getValidRoomId();
        $initialTitle = 'Initial Title That Is Long Enough ' . create_test_uniqid();
        $roomAnnotationId = $repo->addAnnotation(
            $this->getValidUserId(),
            $roomId,
            $this->getValidFileId(),
            AnnotationParam::createFromVarMap(new ArrayVarMap([
                'title' => $initialTitle,
                'highlights_json' => '{"highlights": []}',
                'text' => 'Original body',
            ]))
        );
        $newTitle = 'Updated Title That Is Long Enough ' . create_test_uniqid();
        $newText = 'Updated body ' . create_test_uniqid();

        $repo->updateTitleAndText($roomId, $roomAnnotationId, $newTitle, $newText);

        $all = $repo->getAnnotationsForRoom($roomId);
        $found = null;
        foreach ($all as $view) {
            if ($view->room_annotation_id === $roomAnnotationId) {
                $found = $view;
                break;
            }
        }
        $this->assertNotNull($found);
        $this->assertSame($newTitle, $found->title);
        $this->assertSame($newText, $found->text);
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::updateTitleAndText
     */
    public function test_updateTitleAndText_throws_when_room_annotation_not_found(): void
    {
        $repo = $this->getTestInstance();
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('Annotation not found in room');
        $repo->updateTitleAndText(
            $this->getValidRoomId(),
            '00000000-0000-7000-8000-000000000000',
            'Any Title That Is Long Enough',
            'Any text'
        );
    }

    /**
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\RoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\FakeRoomAnnotationRepo::addAnnotation
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::updateTitleAndText
     * @covers \Bristolian\Repo\RoomAnnotationRepo\PdoRoomAnnotationRepo::addAnnotation
     */
    public function test_updateTitleAndText_throws_when_room_mismatches(): void
    {
        $repo = $this->getTestInstance();
        $roomAnnotationId = $repo->addAnnotation(
            $this->getValidUserId(),
            $this->getValidRoomId(),
            $this->getValidFileId(),
            AnnotationParam::createFromVarMap(new ArrayVarMap([
                'title' => 'Title For Wrong Room Test That Is Long Enough',
                'highlights_json' => '{"highlights": []}',
                'text' => 'Text',
            ]))
        );
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('Annotation not found in room');
        $repo->updateTitleAndText(
            $this->getValidRoomId2(),
            $roomAnnotationId,
            'New Title That Is Long Enough',
            'New text'
        );
    }
}

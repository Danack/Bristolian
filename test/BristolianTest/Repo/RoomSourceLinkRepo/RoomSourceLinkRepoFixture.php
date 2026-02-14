<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomSourceLinkRepo;

use Bristolian\Model\Types\RoomSourceLinkView;
use Bristolian\Parameters\SourceLinkParam;
use Bristolian\Repo\RoomSourceLinkRepo\RoomSourceLinkRepo;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * Abstract test class for RoomSourceLinkRepo implementations.
 *
 * Scenario data (user id, room id, file id) is provided by concrete tests.
 * See docs/refactoring/default_test_scenarios_and_worlds.md § Abstract repo fixtures.
 *
 * @coversNothing
 */
abstract class RoomSourceLinkRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomSourceLinkRepo implementation.
     *
     * @return RoomSourceLinkRepo
     */
    abstract public function getTestInstance(): RoomSourceLinkRepo;

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

    public function test_addSourceLink_returns_room_sourcelink_id(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $room_sourcelink_id = $repo->addSourceLink(
            $this->getValidUserId(),
            $this->getValidRoomId(),
            $this->getValidFileId(),
            $sourceLinkParam
        );

        $this->assertNotEmpty($room_sourcelink_id);
    }

    public function test_addSourceLink_creates_unique_ids(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $id1 = $repo->addSourceLink($this->getValidUserId(), $this->getValidRoomId(), $this->getValidFileId(), $sourceLinkParam);
        $id2 = $repo->addSourceLink($this->getValidUserId(), $this->getValidRoomId(), $this->getValidFileId(), $sourceLinkParam);

        $this->assertNotSame($id1, $id2);
    }

    public function test_getSourceLinksForRoom_returns_links_for_room(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $repo->addSourceLink($this->getValidUserId(), $roomId, $this->getValidFileId(), $sourceLinkParam);

        $links = $repo->getSourceLinksForRoom($roomId);

        $this->assertCount(1, $links);
        $this->assertContainsOnlyInstancesOf(RoomSourceLinkView::class, $links);
        $this->assertSame('Test Source Link Title That Is Long Enough', $links[0]->title);
        $this->assertSame('Test text content', $links[0]->text);
    }

    public function test_getSourceLinksForRoom_returns_only_links_for_specified_room(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam1 = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 1 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 1',
        ]));
        $sourceLinkParam2 = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 2 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 2',
        ]));

        $roomId1 = $this->getValidRoomId();
        $roomId2 = $this->getValidRoomId2();
        $repo->addSourceLink($this->getValidUserId(), $roomId1, $this->getValidFileId(), $sourceLinkParam1);
        $repo->addSourceLink($this->getValidUserId(), $roomId2, $this->getValidFileId(), $sourceLinkParam2);

        $links = $repo->getSourceLinksForRoom($roomId1);

        $this->assertCount(1, $links);
        $this->assertSame('Link 1 Title That Is Long Enough', $links[0]->title);
    }

    public function test_getSourceLinksForRoomAndFile_returns_links_matching_both_room_and_file(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId = $this->getValidFileId();
        $repo->addSourceLink($this->getValidUserId(), $roomId, $fileId, $sourceLinkParam);

        $links = $repo->getSourceLinksForRoomAndFile($roomId, $fileId);

        $this->assertCount(1, $links);
        $this->assertContainsOnlyInstancesOf(RoomSourceLinkView::class, $links);
        $this->assertSame($fileId, $links[0]->file_id);
    }

    public function test_getSourceLinksForRoomAndFile_filters_by_both_room_and_file(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam1 = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 1 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 1',
        ]));
        $sourceLinkParam2 = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Link 2 Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Text 2',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId1 = $this->getValidFileId();
        $fileId2 = $this->getValidFileId2();
        $repo->addSourceLink($this->getValidUserId(), $roomId, $fileId1, $sourceLinkParam1);
        $repo->addSourceLink($this->getValidUserId(), $roomId, $fileId2, $sourceLinkParam2);

        $links = $repo->getSourceLinksForRoomAndFile($roomId, $fileId1);

        $this->assertCount(1, $links);
        $this->assertSame('Link 1 Title That Is Long Enough', $links[0]->title);
        $this->assertSame($fileId1, $links[0]->file_id);
    }

    public function test_getSourceLinksForRoomAndFile_returns_empty_when_room_mismatches(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId = $this->getValidFileId();
        $repo->addSourceLink($this->getValidUserId(), $roomId, $fileId, $sourceLinkParam);

        $links = $repo->getSourceLinksForRoomAndFile('nonexistent-room-id', $fileId);

        $this->assertEmpty($links);
    }

    public function test_getSourceLinksForRoomAndFile_returns_empty_when_file_mismatches(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $roomId = $this->getValidRoomId();
        $fileId = $this->getValidFileId();
        $repo->addSourceLink($this->getValidUserId(), $roomId, $fileId, $sourceLinkParam);

        $links = $repo->getSourceLinksForRoomAndFile($roomId, 'nonexistent-file-id');

        $this->assertEmpty($links);
    }
}

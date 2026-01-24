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
 */
abstract class RoomSourceLinkRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomSourceLinkRepo implementation.
     *
     * @return RoomSourceLinkRepo
     */
    abstract public function getTestInstance(): RoomSourceLinkRepo;

    public function test_addSourceLink_returns_room_sourcelink_id(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $room_sourcelink_id = $repo->addSourceLink(
            'user-123',
            'room-456',
            'file-789',
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

        $id1 = $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam);
        $id2 = $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam);

        $this->assertNotSame($id1, $id2);
    }

    public function test_getSourceLinksForRoom_returns_empty_array_initially(): void
    {
        $repo = $this->getTestInstance();

        $links = $repo->getSourceLinksForRoom('room-123');

        $this->assertIsArray($links);
        $this->assertEmpty($links);
    }

    public function test_getSourceLinksForRoom_returns_links_for_room(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam);

        $links = $repo->getSourceLinksForRoom('room-456');

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

        $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam1);
        $repo->addSourceLink('user-123', 'room-789', 'file-789', $sourceLinkParam2);

        $links = $repo->getSourceLinksForRoom('room-456');

        $this->assertCount(1, $links);
        $this->assertSame('Link 1 Title That Is Long Enough', $links[0]->title);
    }

    public function test_getSourceLinksForRoomAndFile_returns_empty_array_initially(): void
    {
        $repo = $this->getTestInstance();

        $links = $repo->getSourceLinksForRoomAndFile('room-123', 'file-456');

        $this->assertIsArray($links);
        $this->assertEmpty($links);
    }

    public function test_getSourceLinksForRoomAndFile_returns_links_matching_both_room_and_file(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam);

        $links = $repo->getSourceLinksForRoomAndFile('room-456', 'file-789');

        $this->assertCount(1, $links);
        $this->assertContainsOnlyInstancesOf(RoomSourceLinkView::class, $links);
        $this->assertSame('file-789', $links[0]->file_id);
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

        $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam1);
        $repo->addSourceLink('user-123', 'room-456', 'file-999', $sourceLinkParam2);

        $links = $repo->getSourceLinksForRoomAndFile('room-456', 'file-789');

        $this->assertCount(1, $links);
        $this->assertSame('Link 1 Title That Is Long Enough', $links[0]->title);
        $this->assertSame('file-789', $links[0]->file_id);
    }

    public function test_getSourceLinksForRoomAndFile_returns_empty_when_room_mismatches(): void
    {
        $repo = $this->getTestInstance();

        $sourceLinkParam = SourceLinkParam::createFromVarMap(new ArrayVarMap([
            'title' => 'Test Source Link Title That Is Long Enough',
            'highlights_json' => '{"highlights": []}',
            'text' => 'Test text content',
        ]));

        $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam);

        $links = $repo->getSourceLinksForRoomAndFile('room-999', 'file-789');

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

        $repo->addSourceLink('user-123', 'room-456', 'file-789', $sourceLinkParam);

        $links = $repo->getSourceLinksForRoomAndFile('room-456', 'file-999');

        $this->assertEmpty($links);
    }
}

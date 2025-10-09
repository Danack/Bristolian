<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\RoomSourceLink;

/**
 * @coversNothing
 */
class RoomSourceLinkTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\RoomSourceLink
     */
    public function testConstruct()
    {
        $id = 'source-link-123';
        $userId = 'user-456';
        $fileId = 'file-789';
        $highlightsJson = '{"highlights": []}';
        $text = 'Source text';
        $title = 'Source Title';
        $roomSourceLinkId = 'room-source-012';

        $sourceLink = new RoomSourceLink(
            $id,
            $userId,
            $fileId,
            $highlightsJson,
            $text,
            $title,
            $roomSourceLinkId
        );

        $this->assertSame($id, $sourceLink->id);
        $this->assertSame($userId, $sourceLink->user_id);
        $this->assertSame($fileId, $sourceLink->file_id);
        $this->assertSame($highlightsJson, $sourceLink->highlights_json);
        $this->assertSame($text, $sourceLink->text);
        $this->assertSame($title, $sourceLink->title);
        $this->assertSame($roomSourceLinkId, $sourceLink->room_sourcelink_id);
    }

    /**
     * @covers \Bristolian\Model\RoomSourceLink
     */
    public function testToArray()
    {
        $sourceLink = new RoomSourceLink(
            'id',
            'user-id',
            'file-id',
            '{}',
            'text',
            'title',
            'room-id'
        );

        $array = $sourceLink->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('text', $array);
    }
}


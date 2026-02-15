<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\RoomSourceLinkView;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class RoomSourceLinkViewTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\RoomSourceLinkView
     */
    public function test_construct(): void
    {
        $id = 'sourcelink-id';
        $userId = 'user-123';
        $fileId = 'file-456';
        $highlightsJson = '{"highlights":[]}';
        $text = 'Source text';
        $title = 'Source title';
        $roomSourcelinkId = 'room-sourcelink-789';

        $view = new RoomSourceLinkView($id, $userId, $fileId, $highlightsJson, $text, $title, $roomSourcelinkId);

        $this->assertSame($id, $view->id);
        $this->assertSame($userId, $view->user_id);
        $this->assertSame($fileId, $view->file_id);
        $this->assertSame($highlightsJson, $view->highlights_json);
        $this->assertSame($text, $view->text);
        $this->assertSame($title, $view->title);
        $this->assertSame($roomSourcelinkId, $view->room_sourcelink_id);
    }
}

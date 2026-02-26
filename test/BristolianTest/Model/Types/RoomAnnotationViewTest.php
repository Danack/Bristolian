<?php

declare(strict_types = 1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\RoomAnnotationView;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class RoomAnnotationViewTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\RoomAnnotationView
     */
    public function test_construct(): void
    {
        $id = 'annotation-id';
        $userId = 'user-123';
        $fileId = 'file-456';
        $highlightsJson = '{"highlights":[]}';
        $text = 'Source text';
        $title = 'Source title';
        $roomAnnotationId = 'room-annotation-789';

        $view = new RoomAnnotationView($id, $userId, $fileId, $highlightsJson, $text, $title, $roomAnnotationId);

        $this->assertSame($id, $view->id);
        $this->assertSame($userId, $view->user_id);
        $this->assertSame($fileId, $view->file_id);
        $this->assertSame($highlightsJson, $view->highlights_json);
        $this->assertSame($text, $view->text);
        $this->assertSame($title, $view->title);
        $this->assertSame($roomAnnotationId, $view->room_annotation_id);
    }
}

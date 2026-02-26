<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\RoomAnnotation;

/**
 * @coversNothing
 */
class RoomAnnotationTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\RoomAnnotation
     */
    public function testConstruct()
    {
        $this->markTestSkipped("needs fixing");

        $id = 'room-annotation-123';
        $roomId = 'room-456';
        $annotationId = 'annotation-789';
        $title = 'Annotation Title';
        $createdAt = new \DateTimeImmutable();

        $roomAnnotation = new RoomAnnotation($id, $roomId, $annotationId, $title, $createdAt);

        $this->assertSame($id, $roomAnnotation->id);
        $this->assertSame($roomId, $roomAnnotation->room_id);
        $this->assertSame($annotationId, $roomAnnotation->annotation_id);
        $this->assertSame($title, $roomAnnotation->title);
        $this->assertSame($createdAt, $roomAnnotation->created_at);
    }

    /**
     * @covers \Bristolian\Model\Generated\RoomAnnotation
     */
    public function testToArray()
    {
        $this->markTestSkipped("needs fixing");

        $roomAnnotation = new RoomAnnotation(
            'id',
            'room-id',
            'annotation-id',
            'title',
            new \DateTimeImmutable()
        );

        $array = $roomAnnotation->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
    }
}

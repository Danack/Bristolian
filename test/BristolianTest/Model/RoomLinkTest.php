<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\RoomLink;

/**
 * @coversNothing
 */
class RoomLinkTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\RoomLink
     */
    public function testConstruct()
    {
        $id = 'roomlink-123';
        $linkId = 'link-456';
        $url = 'https://example.com';
        $title = 'Example Link';
        $description = 'A test link';
        $roomId = 'room-789';
        $userId = 'user-012';
        $createdAt = new \DateTimeImmutable();

        $roomLink = new RoomLink(
            $id,
            $room_id = $roomId,
            $link_id = $linkId,
            $title,
            $description,
            $created_at = $createdAt
        );

        $this->assertSame($id, $roomLink->id);
        $this->assertSame($linkId, $roomLink->link_id);
//        $this->assertSame($url, $roomLink->url);
        $this->assertSame($title, $roomLink->title);
        $this->assertSame($description, $roomLink->description);
        $this->assertSame($roomId, $roomLink->room_id);
//        $this->assertSame($userId, $roomLink->user_id);
        $this->assertSame($createdAt, $roomLink->created_at);
    }

    /**
     * @covers \RoomLink
     */
    public function testConstructWithNullTitleAndDescription()
    {
        $this->markTestSkipped("fix later");
        $roomLink = new RoomLink(
            'id',
            'link-id',
            'https://example.com',
            null,
            null,
            'room-id',
            'user-id',
            new \DateTimeImmutable()
        );

        $this->assertNull($roomLink->title);
        $this->assertNull($roomLink->description);
    }

    /**
     * @covers \RoomLink
     */
    public function testToArray()
    {
        $this->markTestSkipped("fix later");
        $roomLink = new RoomLink(
            'id',
            'link-id',
            'url',
            'title',
            'desc',
            'room-id',
            'user-id',
            new \DateTimeImmutable()
        );

        $array = $roomLink->toArray();
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('url', $array);
    }
}

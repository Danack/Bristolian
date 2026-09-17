<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use BristolianGenerated\Model\RoomLink;
use Safe\DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\BristolianGenerated\Model\RoomLink::class)]

class RoomLinkTest extends BaseTestCase
{
    public function testConstruct()
    {
        $id = 'roomlink-123';
        $linkId = 'link-456';
        $url = 'https://example.com';
        $title = 'Example Link';
        $description = 'A test link';
        $roomId = 'room-789';
        $userId = 'user-012';
        $createdAt = new DateTimeImmutable();

        $roomLink = new RoomLink(
            $id,
            $room_id = $roomId,
            $link_id = $linkId,
            $title,
            $description,
            $created_at = $createdAt,
            $document_timestamp = null
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
}

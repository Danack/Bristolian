<?php

declare(strict_types=1);

namespace BristolianTest\Model\Types;

use Bristolian\Model\Types\RoomLinkWithUrl;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class RoomLinkWithUrlTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\RoomLinkWithUrl::__construct
     */
    public function test_constructor_sets_all_properties(): void
    {
        $id = 'room-link-id-1';
        $roomId = 'room-1';
        $linkId = 'link-1';
        $url = 'https://example.com/page';
        $title = 'Example title';
        $description = 'Example description';
        $createdAt = new \DateTimeImmutable('2024-03-01 12:00:00');
        $documentTimestamp = new \DateTimeImmutable('2024-03-02 09:30:00');

        $row = new RoomLinkWithUrl(
            $id,
            $roomId,
            $linkId,
            $url,
            $title,
            $description,
            $createdAt,
            $documentTimestamp
        );

        $this->assertSame($id, $row->id);
        $this->assertSame($roomId, $row->room_id);
        $this->assertSame($linkId, $row->link_id);
        $this->assertSame($url, $row->url);
        $this->assertSame($title, $row->title);
        $this->assertSame($description, $row->description);
        $this->assertSame($createdAt, $row->created_at);
        $this->assertSame($documentTimestamp, $row->document_timestamp);
    }

    /**
     * @covers \Bristolian\Model\Types\RoomLinkWithUrl::__construct
     */
    public function test_constructor_accepts_null_title_description_and_document_timestamp(): void
    {
        $createdAt = new \DateTimeImmutable('2025-01-10 08:00:00');

        $row = new RoomLinkWithUrl(
            'rl-2',
            'room-2',
            'link-2',
            'https://example.org/',
            null,
            null,
            $createdAt,
            null
        );

        $this->assertNull($row->title);
        $this->assertNull($row->description);
        $this->assertNull($row->document_timestamp);
        $this->assertSame($createdAt, $row->created_at);
    }
}

<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\Generated\Link;

/**
 * @coversNothing
 */
class LinkTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Generated\Link
     */
    public function testConstruct()
    {
        $id = 'link-123';
        $userId = 'user-456';
        $url = 'https://example.com';
        $createdAt = new \DateTimeImmutable('2025-10-09 12:00:00');

        $link = new Link($id, $userId, $url, $createdAt);

        $this->assertSame($id, $link->id);
        $this->assertSame($userId, $link->user_id);
        $this->assertSame($url, $link->url);
        $this->assertSame($createdAt, $link->created_at);
    }
}

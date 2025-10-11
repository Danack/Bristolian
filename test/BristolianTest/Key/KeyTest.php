<?php

namespace BristolianTest\Key;

use BristolianTest\BaseTestCase;

class KeyTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Keys\ContentSecurityPolicyKey
     * @covers \Bristolian\Keys\PhpBugsMaxCommentStorageKey
     * @covers \Bristolian\Keys\UrlCacheKey
     * @covers \Bristolian\Keys\RoomMessageKey
     */
    public function test_works()
    {
        $result = \Bristolian\Keys\ContentSecurityPolicyKey::getAbsoluteKeyName("some key");
        $result = \Bristolian\Keys\PhpBugsMaxCommentStorageKey::getAbsoluteKeyName();

        $result1 = \Bristolian\Keys\UrlCacheKey::getAbsoluteKeyName("http://example.com/");
        $result2 = \Bristolian\Keys\UrlCacheKey::getAbsoluteKeyName("http://example.com/");

        $result = \Bristolian\Keys\RoomMessageKey::getAbsoluteKeyName();

        $this->assertSame($result1, $result2);
    }
}
<?php

namespace BristolianTest\Key;

use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Keys\ContentSecurityPolicyKey::class)]
#[CoversClass(\Bristolian\Keys\PhpBugsMaxCommentStorageKey::class)]
#[CoversClass(\Bristolian\Keys\RoomMessageKey::class)]
#[CoversClass(\Bristolian\Keys\UrlCacheKey::class)]

class KeyTest extends BaseTestCase
{
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

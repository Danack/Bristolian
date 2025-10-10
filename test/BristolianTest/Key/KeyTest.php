<?php

namespace BristolianTest\Key;

use BristolianTest\BaseTestCase;

class KeyTest extends BaseTestCase
{
    public function test_works()
    {
        $result = \Bristolian\Keys\ContentSecurityPolicyKey::getAbsoluteKeyName("some key");
        $result = \Bristolian\Keys\PhpBugsMaxCommentStorageKey::getAbsoluteKeyName();

        $result1 = \Bristolian\Keys\UrlCacheKey::getAbsoluteKeyName("http://example.com/");
        $result2 = \Bristolian\Keys\UrlCacheKey::getAbsoluteKeyName("http://example.com/");

        $this->assertSame($result1, $result2);
    }
}
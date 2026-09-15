<?php

namespace BristolianTest\SiteHtml;

use Bristolian\SiteHtml\HeaderLink;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\SiteHtml\HeaderLink::class)]
class HeaderLinkTest extends BaseTestCase
{
    public function testWorks()
    {
        $path = '/foo';
        $description = 'Some description';

        $header_link = new HeaderLink($path, $description);

        $this->assertSame($path, $header_link->getPath());
        $this->assertSame($description, $header_link->getDescription());
    }
}

<?php

namespace BristolianTest\SiteHtml;

use Bristolian\SiteHtml\HeaderLink;
use Bristolian\SiteHtml\HeaderLinks;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\SiteHtml\HeaderLinks
 */
class HeaderLinksTest extends BaseTestCase
{
    public function testWorks()
    {
        $path = '/foo';
        $description = 'Some description';
        $header_link = new HeaderLink($path, $description);
        $header_links = new HeaderLinks([$header_link]);
        $header_links_returned = $header_links->getHeaderLinks();
        $this->assertSame($header_links_returned, $header_links);
    }
}

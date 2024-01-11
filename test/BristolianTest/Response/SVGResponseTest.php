<?php

namespace BristolianTest\Response;

use Bristolian\Response\SVGResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\SVGResponse
 */
class SVGResponseTest extends BaseTestCase
{
    public function testWorksCorrectlyWithDefaults()
    {
        // TODO - this should probably contain actual SVG
        // to avoid being confusing.
        $html = "<head><body>Woot, some html.</body>/head>";

        $response = new SVGResponse($html);
        self::assertEquals($html, $response->getBody());
        self::assertEquals(200, $response->getStatus());

        $setHeaders = $response->getHeaders();
        self::assertCount(1, $setHeaders);
        self::assertArrayHasKey('Content-Type', $setHeaders);
        self::assertEquals('image/svg+xml; charset=utf-8', $setHeaders['Content-Type']);
    }

    public function testWorksCorrectlyWithSettings()
    {
        $headers = ['x-foo' => 'x-bar'];
        $html = "<head><body>Woot, some html.</body>/head>";
        $response = new SVGResponse($html, $headers);
        self::assertEquals($html, $response->getBody());

        $setHeaders = $response->getHeaders();
        self::assertCount(2, $setHeaders);

        self::assertArrayHasKey('Content-Type', $setHeaders);
        self::assertEquals('image/svg+xml; charset=utf-8', $setHeaders['Content-Type']);

        self::assertArrayHasKey('x-foo', $setHeaders);
        self::assertEquals('x-bar', $setHeaders['x-foo']);
    }
}

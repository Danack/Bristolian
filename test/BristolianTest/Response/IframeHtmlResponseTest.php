<?php

namespace BristolianTest\Response;

use Bristolian\Response\IframeHtmlResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\IframeHtmlResponse
 */
class IframeHtmlResponseTest extends BaseTestCase
{
    public function testWorksCorrectlyWithDefaults()
    {
        $html = <<< HTML
<!DOCTYPE html>

<html lang="en">
  <body>
    Hello world!
  </body>
</html>
HTML;

        $response = new IframeHtmlResponse($html);
        self::assertEquals($html, $response->getBody());
        self::assertEquals(200, $response->getStatus());

        $setHeaders = $response->getHeaders();
        self::assertCount(1, $setHeaders);
        self::assertArrayHasKey('Content-Type', $setHeaders);
        self::assertEquals('text/html', $setHeaders['Content-Type']);
    }
}

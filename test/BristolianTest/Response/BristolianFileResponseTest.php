<?php

namespace BristolianTest\Response;

use Bristolian\Response\BristolianFileResponse;
use BristolianTest\BaseTestCase;
use Bristolian\Exception\BristolianResponseException;

/**
 * @covers \Bristolian\Response\BristolianFileResponse
 */
class BristolianFileResponseTest extends BaseTestCase
{
    public function testWorksCorrectlyWithDefaults()
    {
        $filepath = __DIR__ . "/../../sample.pdf";

        $response = new BristolianFileResponse($filepath);
        self::assertEquals(\Safe\file_get_contents($filepath), $response->getBody());
        self::assertEquals(200, $response->getStatus());

        $setHeaders = $response->getHeaders();
        self::assertCount(1, $setHeaders);
        self::assertArrayHasKey('Content-Type', $setHeaders);
        self::assertEquals('application/pdf', $setHeaders['Content-Type']);
    }


    public function testErrorsCorrectly_bad_file()
    {
        $this->expectExceptionMessageMatchesTemplateString(
            BristolianResponseException::FAILED_TO_OPEN_FILE
        );
        $this->expectException(BristolianResponseException::class);
        new BristolianFileResponse("File-doesnt-exist.pdf");
    }
}

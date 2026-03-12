<?php

namespace BristolianTest\Response;

use Bristolian\Response\RoomFileUploadErrorResponse;
use Bristolian\Service\RoomFileStorage\UploadError;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class RoomFileUploadErrorResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\RoomFileUploadErrorResponse::__construct
     * @covers \Bristolian\Response\RoomFileUploadErrorResponse::getStatus
     */
    public function testGetStatusReturns400(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new RoomFileUploadErrorResponse($error);

        $this->assertSame(400, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\RoomFileUploadErrorResponse::getHeaders
     */
    public function testGetHeadersReturnsContentType(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new RoomFileUploadErrorResponse($error);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\RoomFileUploadErrorResponse::getBody
     */
    public function testGetBodyReturnsErrorJson(): void
    {
        $error = UploadError::unsupportedFileType();
        $response = new RoomFileUploadErrorResponse($error);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['result']);
        $this->assertSame(UploadError::UNSUPPORTED_FILE_TYPE, $decoded['error']);
    }
}

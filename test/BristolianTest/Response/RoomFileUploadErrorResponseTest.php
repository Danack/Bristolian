<?php

namespace BristolianTest\Response;

use Bristolian\Response\RoomFileUploadErrorResponse;
use Bristolian\Service\RoomFileStorage\UploadError;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Response\RoomFileUploadErrorResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Response\RoomFileUploadErrorResponse::class, 'getBody')]
#[CoversMethod(\Bristolian\Response\RoomFileUploadErrorResponse::class, 'getHeaders')]
#[CoversMethod(\Bristolian\Response\RoomFileUploadErrorResponse::class, 'getStatus')]

class RoomFileUploadErrorResponseTest extends BaseTestCase
{
    public function testGetStatusReturns400(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new RoomFileUploadErrorResponse($error);

        $this->assertSame(400, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType(): void
    {
        $error = UploadError::uploadedFileUnreadable();
        $response = new RoomFileUploadErrorResponse($error);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

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

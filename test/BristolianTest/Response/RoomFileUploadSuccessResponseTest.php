<?php

namespace BristolianTest\Response;

use Bristolian\Response\RoomFileUploadSuccessResponse;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Response\RoomFileUploadSuccessResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Response\RoomFileUploadSuccessResponse::class, 'getBody')]
#[CoversMethod(\Bristolian\Response\RoomFileUploadSuccessResponse::class, 'getHeaders')]
#[CoversMethod(\Bristolian\Response\RoomFileUploadSuccessResponse::class, 'getStatus')]

class RoomFileUploadSuccessResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200(): void
    {
        $response = new RoomFileUploadSuccessResponse('file-123');

        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType(): void
    {
        $response = new RoomFileUploadSuccessResponse('file-123');
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsSuccessJsonWithFileId(): void
    {
        $response = new RoomFileUploadSuccessResponse('file-456');
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertSame('file-456', $decoded['file_id']);
    }
}

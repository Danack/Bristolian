<?php

namespace BristolianTest\Response;

use Bristolian\Response\RoomFileUploadSuccessResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class RoomFileUploadSuccessResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\RoomFileUploadSuccessResponse::__construct
     * @covers \Bristolian\Response\RoomFileUploadSuccessResponse::getStatus
     */
    public function testGetStatusReturns200(): void
    {
        $response = new RoomFileUploadSuccessResponse('file-123');

        $this->assertSame(200, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\RoomFileUploadSuccessResponse::getHeaders
     */
    public function testGetHeadersReturnsContentType(): void
    {
        $response = new RoomFileUploadSuccessResponse('file-123');
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\RoomFileUploadSuccessResponse::getBody
     */
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

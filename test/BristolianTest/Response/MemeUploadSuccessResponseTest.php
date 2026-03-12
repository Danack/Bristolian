<?php

namespace BristolianTest\Response;

use Bristolian\Response\MemeUploadSuccessResponse;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class MemeUploadSuccessResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\MemeUploadSuccessResponse::__construct
     * @covers \Bristolian\Response\MemeUploadSuccessResponse::getStatus
     */
    public function testGetStatusReturns200(): void
    {
        $meme = new ObjectStoredMeme('normalized.jpg', 'meme-123');
        $response = new MemeUploadSuccessResponse($meme);

        $this->assertSame(200, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\MemeUploadSuccessResponse::getHeaders
     */
    public function testGetHeadersReturnsContentType(): void
    {
        $meme = new ObjectStoredMeme('normalized.jpg', 'meme-123');
        $response = new MemeUploadSuccessResponse($meme);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\MemeUploadSuccessResponse::getBody
     */
    public function testGetBodyReturnsSuccessJsonWithMemeId(): void
    {
        $meme = new ObjectStoredMeme('normalized.jpg', 'meme-456');
        $response = new MemeUploadSuccessResponse($meme);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertSame('actually upload to file_server.', $decoded['next']);
        $this->assertSame('meme-456', $decoded['meme_id']);
    }
}

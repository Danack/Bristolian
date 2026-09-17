<?php

namespace BristolianTest\Response;

use Bristolian\Response\MemeUploadSuccessResponse;
use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Response\MemeUploadSuccessResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Response\MemeUploadSuccessResponse::class, 'getBody')]
#[CoversMethod(\Bristolian\Response\MemeUploadSuccessResponse::class, 'getHeaders')]
#[CoversMethod(\Bristolian\Response\MemeUploadSuccessResponse::class, 'getStatus')]

class MemeUploadSuccessResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200(): void
    {
        $meme = new ObjectStoredMeme('normalized.jpg', 'meme-123');
        $response = new MemeUploadSuccessResponse($meme);

        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType(): void
    {
        $meme = new ObjectStoredMeme('normalized.jpg', 'meme-123');
        $response = new MemeUploadSuccessResponse($meme);
        $headers = $response->getHeaders();

        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

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

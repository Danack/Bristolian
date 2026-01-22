<?php

namespace BristolianTest\Response;

use Bristolian\Model\Generated\MemeText;
use Bristolian\Response\GetMemeTextResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\GetMemeTextResponse
 */
class GetMemeTextResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $memeText = new MemeText(
            id: 1,
            text: 'Some meme text',
            meme_id: 'meme-123',
            created_at: new \DateTimeImmutable()
        );
        $response = new GetMemeTextResponse($memeText);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetStatusReturns200WithNull()
    {
        $response = new GetMemeTextResponse(null);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $memeText = new MemeText(
            id: 1,
            text: 'Some meme text',
            meme_id: 'meme-123',
            created_at: new \DateTimeImmutable()
        );
        $response = new GetMemeTextResponse($memeText);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsMemeText()
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $memeText = new MemeText(
            id: 1,
            text: 'Some meme text',
            meme_id: 'meme-123',
            created_at: $createdAt
        );
        $response = new GetMemeTextResponse($memeText);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('meme_text', $decoded['data']);
        $this->assertSame(1, $decoded['data']['meme_text']['id']);
        $this->assertSame('Some meme text', $decoded['data']['meme_text']['text']);
        $this->assertSame('meme-123', $decoded['data']['meme_text']['meme_id']);
    }

    public function testGetBodyReturnsNullWhenMemeTextIsNull()
    {
        $response = new GetMemeTextResponse(null);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertNull($decoded['data']['meme_text']);
    }
}

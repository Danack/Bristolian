<?php

namespace BristolianTest\Response\TinnedFish;

use Bristolian\Response\TinnedFish\GenerateApiTokenResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\TinnedFish\GenerateApiTokenResponse
 */
class GenerateApiTokenResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $response = new GenerateApiTokenResponse(
            'token-123',
            'My API Token',
            'https://example.com/qr.png',
            $createdAt
        );
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $response = new GenerateApiTokenResponse(
            'token-123',
            'My API Token',
            'https://example.com/qr.png',
            $createdAt
        );
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsTokenInfo()
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $response = new GenerateApiTokenResponse(
            'token-123',
            'My API Token',
            'https://example.com/qr.png',
            $createdAt
        );
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertSame('token-123', $decoded['token']);
        $this->assertSame('My API Token', $decoded['name']);
        $this->assertSame('https://example.com/qr.png', $decoded['qr_code_url']);
        $this->assertArrayHasKey('created_at', $decoded);
    }
}

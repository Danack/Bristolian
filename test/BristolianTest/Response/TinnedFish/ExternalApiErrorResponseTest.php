<?php

namespace BristolianTest\Response\TinnedFish;

use Bristolian\Model\TinnedFish\ProductError;
use Bristolian\Response\TinnedFish\ExternalApiErrorResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\TinnedFish\ExternalApiErrorResponse
 */
class ExternalApiErrorResponseTest extends BaseTestCase
{
    public function testGetStatusReturns502()
    {
        $response = new ExternalApiErrorResponse('1234567890123', 'Network timeout');
        
        $this->assertSame(502, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $response = new ExternalApiErrorResponse('1234567890123', 'Network timeout');
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsError()
    {
        $response = new ExternalApiErrorResponse('1234567890123', 'Network timeout');
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertSame('EXTERNAL_API_ERROR', $decoded['error']['code']);
        $this->assertSame('1234567890123', $decoded['error']['barcode']);
        $this->assertSame('Network timeout', $decoded['error']['details']);
    }
}

<?php

namespace BristolianTest\Response\TinnedFish;

use Bristolian\Response\TinnedFish\ProductNotFoundResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\TinnedFish\ProductNotFoundResponse
 */
class ProductNotFoundResponseTest extends BaseTestCase
{
    public function testGetStatusReturns404()
    {
        $response = new ProductNotFoundResponse('1234567890123');
        
        $this->assertSame(404, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $response = new ProductNotFoundResponse('1234567890123');
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsError()
    {
        $response = new ProductNotFoundResponse('1234567890123');
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertSame('PRODUCT_NOT_FOUND', $decoded['error']['code']);
        $this->assertSame('1234567890123', $decoded['error']['barcode']);
    }
}

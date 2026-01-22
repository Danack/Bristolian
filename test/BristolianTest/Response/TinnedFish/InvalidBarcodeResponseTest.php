<?php

namespace BristolianTest\Response\TinnedFish;

use Bristolian\Response\TinnedFish\InvalidBarcodeResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\TinnedFish\InvalidBarcodeResponse
 */
class InvalidBarcodeResponseTest extends BaseTestCase
{
    public function testGetStatusReturns400()
    {
        $response = new InvalidBarcodeResponse('invalid-barcode');
        
        $this->assertSame(400, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $response = new InvalidBarcodeResponse('invalid-barcode');
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsError()
    {
        $response = new InvalidBarcodeResponse('invalid-barcode');
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertFalse($decoded['success']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertSame('INVALID_BARCODE', $decoded['error']['code']);
        $this->assertSame('invalid-barcode', $decoded['error']['barcode']);
    }
}

<?php

namespace BristolianTest\Response\TinnedFish;

use Bristolian\Model\TinnedFish\Copyright;
use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Response\TinnedFish\ProductLookupResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\TinnedFish\ProductLookupResponse
 */
class ProductLookupResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $product = new Product(
            barcode: '1234567890123',
            name: 'Test Product',
            brand: 'Test Brand',
            species: null,
            weight: null,
            weight_drained: null,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED
        );
        $response = new ProductLookupResponse('canonical', $product);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $product = new Product(
            barcode: '1234567890123',
            name: 'Test Product',
            brand: 'Test Brand',
            species: null,
            weight: null,
            weight_drained: null,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED
        );
        $response = new ProductLookupResponse('canonical', $product);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsProduct()
    {
        $product = new Product(
            barcode: '1234567890123',
            name: 'Test Product',
            brand: 'Test Brand',
            species: 'Tuna',
            weight: 100.0,
            weight_drained: 80.0,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED
        );
        $response = new ProductLookupResponse('canonical', $product);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertSame('canonical', $decoded['source']);
        $this->assertArrayHasKey('product', $decoded);
        $this->assertSame('1234567890123', $decoded['product']['barcode']);
        $this->assertNull($decoded['copyright']);
    }

    public function testGetBodyWithCopyright()
    {
        $product = new Product(
            barcode: '1234567890123',
            name: 'Test Product',
            brand: 'Test Brand',
            species: null,
            weight: null,
            weight_drained: null,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED
        );
        $copyright = Copyright::openFoodFacts();
        $response = new ProductLookupResponse('external', $product, $copyright);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertSame('external', $decoded['source']);
        $this->assertArrayHasKey('copyright', $decoded);
        $this->assertSame('OpenFoodFacts', $decoded['copyright']['source']);
    }
}

<?php

namespace BristolianTest\Response\TinnedFish;

use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Response\TinnedFish\GetAllProductsResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\TinnedFish\GetAllProductsResponse
 */
class GetAllProductsResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $products = [];
        $response = new GetAllProductsResponse($products);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $products = [];
        $response = new GetAllProductsResponse($products);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsProducts()
    {
        $product1 = new Product(
            barcode: '1234567890123',
            name: 'Test Product 1',
            brand: 'Test Brand',
            species: 'Tuna',
            weight: 100.0,
            weight_drained: 80.0,
            product_code: 'TP1',
            image_url: 'https://example.com/image1.jpg',
            validation_status: ValidationStatus::VALIDATED_IS_FISH,
            raw_data: null,
            created_at: new \DateTimeImmutable('2024-01-15 12:00:00'),
            updated_at: null
        );
        
        $product2 = new Product(
            barcode: '9876543210987',
            name: 'Test Product 2',
            brand: 'Test Brand 2',
            species: null,
            weight: null,
            weight_drained: null,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: null,
            updated_at: null
        );
        
        $products = [$product1, $product2];
        $response = new GetAllProductsResponse($products);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertArrayHasKey('products', $decoded);
        $this->assertCount(2, $decoded['products']);
        $this->assertSame('1234567890123', $decoded['products'][0]['barcode']);
        $this->assertSame('Test Product 1', $decoded['products'][0]['name']);
        $this->assertSame('validated_is_fish', $decoded['products'][0]['validation_status']);
    }

    public function testGetBodyWithEmptyProducts()
    {
        $products = [];
        $response = new GetAllProductsResponse($products);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertCount(0, $decoded['products']);
    }
}

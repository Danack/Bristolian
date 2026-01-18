<?php

declare(strict_types=1);

namespace BristolianTest\ApiController;

use Bristolian\ApiController\TinnedFish;
use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo;
use Bristolian\Response\TinnedFish\GetAllProductsResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\ApiController\TinnedFish::getAllProducts
 */
class TinnedFishTest extends BaseTestCase
{
    public function test_getAllProducts_returns_empty_array_when_no_products(): void
    {
        $repo = new FakeTinnedFishProductRepo();
        $controller = new TinnedFish();

        $response = $controller->getAllProducts($repo);

        $this->assertInstanceOf(GetAllProductsResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('products', $data);
        $this->assertIsArray($data['products']);
        $this->assertCount(0, $data['products']);
    }

    public function test_getAllProducts_returns_all_products(): void
    {
        $now = new \DateTimeImmutable();
        $product1 = new Product(
            barcode: '1234567890123',
            name: 'Sardines in Olive Oil',
            brand: 'Test Brand',
            species: 'Sardines',
            weight: 125.0,
            weight_drained: 90.0,
            product_code: 'PROD-001',
            image_url: 'https://example.com/image1.jpg',
            validation_status: ValidationStatus::VALIDATED_IS_FISH,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $product2 = new Product(
            barcode: '9876543210987',
            name: 'Tuna in Brine',
            brand: 'Another Brand',
            species: 'Tuna',
            weight: 200.0,
            weight_drained: 150.0,
            product_code: 'PROD-002',
            image_url: 'https://example.com/image2.jpg',
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo = new FakeTinnedFishProductRepo([$product1, $product2]);
        $controller = new TinnedFish();

        $response = $controller->getAllProducts($repo);

        $this->assertInstanceOf(GetAllProductsResponse::class, $response);
        $this->assertSame(200, $response->getStatus());

        $data = json_decode_safe($response->getBody());
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('products', $data);
        $this->assertIsArray($data['products']);
        $this->assertCount(2, $data['products']);

        // Check first product
        $firstProduct = $data['products'][0];
        $this->assertSame('1234567890123', $firstProduct['barcode']);
        $this->assertSame('Sardines in Olive Oil', $firstProduct['name']);
        $this->assertSame('Test Brand', $firstProduct['brand']);
        $this->assertSame('Sardines', $firstProduct['species']);
        $this->assertSame(125.0, $firstProduct['weight']);
        $this->assertSame(90.0, $firstProduct['weight_drained']);
        $this->assertSame('PROD-001', $firstProduct['product_code']);
        $this->assertSame('https://example.com/image1.jpg', $firstProduct['image_url']);
        $this->assertSame(ValidationStatus::VALIDATED_IS_FISH->value, $firstProduct['validation_status']);
        $this->assertNotEmpty($firstProduct['created_at']);

        // Check second product
        $secondProduct = $data['products'][1];
        $this->assertSame('9876543210987', $secondProduct['barcode']);
        $this->assertSame('Tuna in Brine', $secondProduct['name']);
        $this->assertSame('Another Brand', $secondProduct['brand']);
        $this->assertSame('Tuna', $secondProduct['species']);
        $this->assertSame(200.0, $secondProduct['weight']);
        $this->assertSame(150.0, $secondProduct['weight_drained']);
        $this->assertSame('PROD-002', $secondProduct['product_code']);
        $this->assertSame('https://example.com/image2.jpg', $secondProduct['image_url']);
        $this->assertSame(ValidationStatus::NOT_VALIDATED->value, $secondProduct['validation_status']);
        $this->assertNotEmpty($secondProduct['created_at']);
    }
}

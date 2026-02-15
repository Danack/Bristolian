<?php

declare(strict_types = 1);

namespace BristolianTest\Model\TinnedFish;

use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class ProductTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\TinnedFish\Product
     */
    public function test_construct(): void
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 10:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-16 11:00:00');

        $product = new Product(
            barcode: '1234567890123',
            name: 'Tuna Chunks',
            brand: 'Test Brand',
            species: 'Tuna',
            weight: 200.0,
            weight_drained: 140.0,
            product_code: 'ABC123',
            image_url: 'https://example.com/image.jpg',
            validation_status: ValidationStatus::VALIDATED_IS_FISH,
            raw_data: ['key' => 'value'],
            created_at: $createdAt,
            updated_at: $updatedAt
        );

        $this->assertSame('1234567890123', $product->barcode);
        $this->assertSame('Tuna Chunks', $product->name);
        $this->assertSame('Test Brand', $product->brand);
        $this->assertSame('Tuna', $product->species);
        $this->assertSame(200.0, $product->weight);
        $this->assertSame(140.0, $product->weight_drained);
        $this->assertSame('ABC123', $product->product_code);
        $this->assertSame('https://example.com/image.jpg', $product->image_url);
        $this->assertSame(ValidationStatus::VALIDATED_IS_FISH, $product->validation_status);
        $this->assertSame(['key' => 'value'], $product->raw_data);
        $this->assertSame($createdAt, $product->created_at);
        $this->assertSame($updatedAt, $product->updated_at);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\Product
     */
    public function test_construct_with_defaults(): void
    {
        $product = new Product(
            barcode: '1234567890123',
            name: 'Product',
            brand: 'Brand',
            species: null,
            weight: null,
            weight_drained: null,
            product_code: null,
            image_url: null
        );

        $this->assertSame('1234567890123', $product->barcode);
        $this->assertSame(ValidationStatus::NOT_VALIDATED, $product->validation_status);
        $this->assertNull($product->raw_data);
        $this->assertNull($product->created_at);
        $this->assertNull($product->updated_at);
    }
}

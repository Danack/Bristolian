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

    /**
     * @covers \Bristolian\Model\TinnedFish\Product::fromRow
     */
    public function test_fromRow(): void
    {
        $row = [
            'barcode' => '5012345678901',
            'name' => 'Anchovy Fillets',
            'brand' => 'Ocean Brand',
            'species' => 'Anchovy',
            'weight' => 100.5,
            'weight_drained' => 65.0,
            'product_code' => 'XYZ789',
            'image_url' => 'https://example.com/anchovy.jpg',
            'validation_status' => 'validated_is_fish',
            'created_at' => '2024-02-01 09:00:00',
            'updated_at' => '2024-02-02 10:30:00',
        ];

        $product = Product::fromRow($row);

        $this->assertSame('5012345678901', $product->barcode);
        $this->assertSame('Anchovy Fillets', $product->name);
        $this->assertSame('Ocean Brand', $product->brand);
        $this->assertSame('Anchovy', $product->species);
        $this->assertSame(100.5, $product->weight);
        $this->assertSame(65.0, $product->weight_drained);
        $this->assertSame('XYZ789', $product->product_code);
        $this->assertSame('https://example.com/anchovy.jpg', $product->image_url);
        $this->assertSame(ValidationStatus::VALIDATED_IS_FISH, $product->validation_status);
        $this->assertNull($product->raw_data);
        $this->assertEquals(new \DateTimeImmutable('2024-02-01 09:00:00'), $product->created_at);
        $this->assertEquals(new \DateTimeImmutable('2024-02-02 10:30:00'), $product->updated_at);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\Product::fromRow
     */
    public function test_fromRow_with_null_weight_and_no_validation_status(): void
    {
        $row = [
            'barcode' => '9999999999999',
            'name' => 'Mystery Tin',
            'brand' => 'Unknown',
            'species' => null,
            'weight' => null,
            'weight_drained' => null,
            'product_code' => null,
            'image_url' => null,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];

        $product = Product::fromRow($row);

        $this->assertSame('9999999999999', $product->barcode);
        $this->assertNull($product->species);
        $this->assertNull($product->weight);
        $this->assertNull($product->weight_drained);
        $this->assertNull($product->product_code);
        $this->assertNull($product->image_url);
        $this->assertSame(ValidationStatus::NOT_VALIDATED, $product->validation_status);
    }
}

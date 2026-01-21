<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for TinnedFishProductRepo implementations.
 */
abstract class TinnedFishProductRepoTest extends BaseTestCase
{
    /**
     * Get a test instance of the TinnedFishProductRepo implementation.
     *
     * @return TinnedFishProductRepo
     */
    abstract public function getTestInstance(): TinnedFishProductRepo;

    public function test_getByBarcode_returns_null_for_nonexistent_barcode(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getByBarcode('9999999999999');
        $this->assertNull($result);
    }


    public function test_getByBarcode_returns_null_for_empty_barcode(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getByBarcode('');
        $this->assertNull($result);
    }

    public function test_getAll_returns_empty_array_initially(): void
    {
        $repo = $this->getTestInstance();

        $products = $repo->getAll();
        $this->assertIsArray($products);
        $this->assertEmpty($products);
    }

    public function test_save_and_getByBarcode(): void
    {
        $repo = $this->getTestInstance();

        $now = new \DateTimeImmutable();
        $product = new Product(
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

        $repo->save($product);

        $found = $repo->getByBarcode('1234567890123');
        $this->assertNotNull($found);
        $this->assertInstanceOf(Product::class, $found);
        $this->assertSame('1234567890123', $found->barcode);
        $this->assertSame('Sardines in Olive Oil', $found->name);
    }

    public function test_save_updates_existing_product(): void
    {
        $repo = $this->getTestInstance();

        $now = new \DateTimeImmutable();
        $product1 = new Product(
            barcode: '1234567890123',
            name: 'Original Name',
            brand: 'Test Brand',
            species: 'Sardines',
            weight: 125.0,
            weight_drained: 90.0,
            product_code: 'PROD-001',
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo->save($product1);

        // Update with new information
        $product2 = new Product(
            barcode: '1234567890123',
            name: 'Updated Name',
            brand: 'New Brand',
            species: 'Tuna',
            weight: 200.0,
            weight_drained: 150.0,
            product_code: 'PROD-002',
            image_url: 'https://example.com/image.jpg',
            validation_status: ValidationStatus::VALIDATED_IS_FISH,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo->save($product2);

        $found = $repo->getByBarcode('1234567890123');
        $this->assertNotNull($found);
        $this->assertSame('Updated Name', $found->name);
        $this->assertSame('New Brand', $found->brand);
    }

    public function test_updateValidationStatus(): void
    {
        $repo = $this->getTestInstance();

        $now = new \DateTimeImmutable();
        $product = new Product(
            barcode: '1234567890123',
            name: 'Test Product',
            brand: 'Test Brand',
            species: 'Sardines',
            weight: 125.0,
            weight_drained: 90.0,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo->save($product);

        // Update validation status
        $repo->updateValidationStatus('1234567890123', ValidationStatus::VALIDATED_IS_FISH);

        $updated = $repo->getByBarcode('1234567890123');
        $this->assertNotNull($updated);
        $this->assertSame(ValidationStatus::VALIDATED_IS_FISH, $updated->validation_status);
    }

    public function test_getAll_returns_saved_products(): void
    {
        $repo = $this->getTestInstance();

        $now = new \DateTimeImmutable();
        $product1 = new Product(
            barcode: '1234567890123',
            name: 'Product 1',
            brand: 'Brand 1',
            species: 'Sardines',
            weight: 125.0,
            weight_drained: 90.0,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $product2 = new Product(
            barcode: '9876543210987',
            name: 'Product 2',
            brand: 'Brand 2',
            species: 'Tuna',
            weight: 200.0,
            weight_drained: 150.0,
            product_code: null,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo->save($product1);
        $repo->save($product2);

        $allProducts = $repo->getAll();
        $this->assertCount(2, $allProducts);

        $barcodes = array_map(fn(Product $p) => $p->barcode, $allProducts);
        $this->assertContains('1234567890123', $barcodes);
        $this->assertContains('9876543210987', $barcodes);
    }
}

<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for TinnedFishProductRepo implementations.
 *
 * @coversNothing
 */
abstract class TinnedFishProductRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the TinnedFishProductRepo implementation.
     *
     * @return TinnedFishProductRepo
     */
    abstract public function getTestInstance(): TinnedFishProductRepo;

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::__construct
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
    public function test_getByBarcode_returns_null_for_nonexistent_barcode(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getByBarcode('9999999999999');
        $this->assertNull($result);
    }


    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
    public function test_getByBarcode_returns_null_for_empty_barcode(): void
    {
        $repo = $this->getTestInstance();

        $result = $repo->getByBarcode('');
        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
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

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
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

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::updateValidationStatus
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::updateValidationStatus
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::getByBarcode
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::updateValidationStatus
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
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

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::getAll
     * @covers \Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::getAll
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getAll
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::save
     */
    public function test_getAll_returns_saved_products(): void
    {
        $repo = $this->getTestInstance();

        // barcode column is varchar(20) - use short unique values
        $barcode1 = '1' . str_pad((string) random_int(100000000, 999999999), 9, '0');
        $name1 = 'Product ' . create_test_uniqid();
        $brand1 = 'Brand ' . create_test_uniqid();
        $species1 = 'Sardines';
        $product_code1 = 'PROD-' . create_test_uniqid();

        $barcode2 = '2' . str_pad((string) random_int(100000000, 999999999), 9, '0');
        $name2 = 'Product ' . create_test_uniqid();
        $brand2 = 'Brand ' . create_test_uniqid();
        $species2 = 'Tuna';
        $product_code2 = 'PROD-' . create_test_uniqid();

        $now = new \DateTimeImmutable();
        $product1 = new Product(
            barcode: $barcode1,
            name: $name1,
            brand: $brand1,
            species: $species1,
            weight: 125.0,
            weight_drained: 90.0,
            product_code: $product_code1,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $product2 = new Product(
            barcode: $barcode2,
            name: $name2,
            brand: $brand2,
            species: $species2,
            weight: 200.0,
            weight_drained: 150.0,
            product_code: $product_code2,
            image_url: null,
            validation_status: ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: $now,
            updated_at: $now
        );

        $repo->save($product1);
        $repo->save($product2);

        $allProducts = $repo->getAll();

        // Find the products by their unique barcodes
        $found1 = null;
        $found2 = null;
        foreach ($allProducts as $product) {
            if ($product->barcode === $barcode1) {
                $found1 = $product;
            }
            if ($product->barcode === $barcode2) {
                $found2 = $product;
            }
        }

        $this->assertNotNull($found1, 'First product should be found by unique barcode');
        $this->assertSame($barcode1, $found1->barcode);
        $this->assertSame($name1, $found1->name);
        $this->assertSame($brand1, $found1->brand);
        $this->assertSame($species1, $found1->species);
        $this->assertSame(125.0, $found1->weight);
        $this->assertSame(90.0, $found1->weight_drained);
        $this->assertSame($product_code1, $found1->product_code);

        $this->assertNotNull($found2, 'Second product should be found by unique barcode');
        $this->assertSame($barcode2, $found2->barcode);
        $this->assertSame($name2, $found2->name);
        $this->assertSame($brand2, $found2->brand);
        $this->assertSame($species2, $found2->species);
        $this->assertSame(200.0, $found2->weight);
        $this->assertSame(150.0, $found2->weight_drained);
        $this->assertSame($product_code2, $found2->product_code);
    }
}

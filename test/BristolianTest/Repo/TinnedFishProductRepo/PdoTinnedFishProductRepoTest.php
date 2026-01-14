<?php

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Model\TinnedFish\Product;
use Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

/**
 * Tests for PdoTinnedFishProductRepo
 *
 * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo
 */
class PdoTinnedFishProductRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo
     */
    public function test_constructor(): void
    {
        $repo = $this->injector->make(PdoTinnedFishProductRepo::class);
        $this->assertInstanceOf(PdoTinnedFishProductRepo::class, $repo);
    }

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
    public function test_getByBarcode_returns_null_for_nonexistent_barcode(): void
    {
        $repo = $this->injector->make(PdoTinnedFishProductRepo::class);

        $result = $repo->getByBarcode('9999999999999');

        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
    public function test_getByBarcode_returns_null_for_empty_barcode(): void
    {
        $repo = $this->injector->make(PdoTinnedFishProductRepo::class);

        $result = $repo->getByBarcode('');

        $this->assertNull($result);
    }

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getByBarcode
     */
    public function test_save_and_getByBarcode(): void
    {
        $repo = $this->injector->make(PdoTinnedFishProductRepo::class);

        $barcode = '1234567890123';
        $product = new Product(
            barcode: $barcode,
            name: 'Test Sardines',
            brand: 'Test Brand',
            species: 'Sardines',
            weight: 125.0,
            weight_drained: 90.0,
            product_code: 'TEST-001',
            image_url: 'https://example.com/image.jpg',
        );

        $repo->save($product);

        $retrieved = $repo->getByBarcode($barcode);

        $this->assertNotNull($retrieved);
        $this->assertSame($barcode, $retrieved->barcode);
        $this->assertSame('Test Sardines', $retrieved->name);
        $this->assertSame('Test Brand', $retrieved->brand);
        $this->assertSame('Sardines', $retrieved->species);
        $this->assertSame(125.0, $retrieved->weight);
        $this->assertSame(90.0, $retrieved->weight_drained);
        $this->assertSame('TEST-001', $retrieved->product_code);
        $this->assertSame('https://example.com/image.jpg', $retrieved->image_url);
    }

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::save
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::getAll
     */
    public function test_save_and_getAll(): void
    {
        $repo = $this->injector->make(PdoTinnedFishProductRepo::class);

        $product1 = new Product(
            barcode: '1111111111111',
            name: 'Sardines in Oil',
            brand: 'Brand A',
            species: 'Sardines',
            weight: 100.0,
            weight_drained: null,
            product_code: null,
            image_url: null,
        );

        $product2 = new Product(
            barcode: '2222222222222',
            name: 'Tuna in Water',
            brand: 'Brand B',
            species: 'Tuna',
            weight: 150.0,
            weight_drained: 120.0,
            product_code: null,
            image_url: null,
        );

        $repo->save($product1);
        $repo->save($product2);

        $allProducts = $repo->getAll();

        $this->assertGreaterThanOrEqual(2, count($allProducts));

        $barcodes = array_map(fn(Product $p) => $p->barcode, $allProducts);
        $this->assertContains('1111111111111', $barcodes);
        $this->assertContains('2222222222222', $barcodes);
    }

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\PdoTinnedFishProductRepo::save
     */
    public function test_save_updates_existing_product(): void
    {
        $repo = $this->injector->make(PdoTinnedFishProductRepo::class);

        $barcode = '3333333333333';

        // Save initial product
        $product = new Product(
            barcode: $barcode,
            name: 'Original Name',
            brand: 'Original Brand',
            species: 'Sardines',
            weight: 100.0,
            weight_drained: null,
            product_code: null,
            image_url: null,
        );
        $repo->save($product);

        // Save updated product with same barcode
        $updatedProduct = new Product(
            barcode: $barcode,
            name: 'Updated Name',
            brand: 'Updated Brand',
            species: 'Mackerel',
            weight: 200.0,
            weight_drained: 150.0,
            product_code: 'UPD-001',
            image_url: 'https://example.com/updated.jpg',
        );
        $repo->save($updatedProduct);

        $retrieved = $repo->getByBarcode($barcode);

        $this->assertNotNull($retrieved);
        $this->assertSame('Updated Name', $retrieved->name);
        $this->assertSame('Updated Brand', $retrieved->brand);
        $this->assertSame('Mackerel', $retrieved->species);
        $this->assertSame(200.0, $retrieved->weight);
        $this->assertSame(150.0, $retrieved->weight_drained);
        $this->assertSame('UPD-001', $retrieved->product_code);
        $this->assertSame('https://example.com/updated.jpg', $retrieved->image_url);
    }
}

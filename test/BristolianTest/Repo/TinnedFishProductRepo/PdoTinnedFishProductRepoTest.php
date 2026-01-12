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
}

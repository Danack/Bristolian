<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\TinnedFishProductRepo;

use Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeTinnedFishProductRepoTest extends TinnedFishProductRepoFixture
{
    /**
     * @return TinnedFishProductRepo
     */
    public function getTestInstance(): TinnedFishProductRepo
    {
        return new FakeTinnedFishProductRepo([]);
    }

    /**
     * @covers \Bristolian\Repo\TinnedFishProductRepo\FakeTinnedFishProductRepo::__construct
     */
    public function test_constructor_with_initial_products(): void
    {
        $product = new \Bristolian\Model\TinnedFish\Product(
            barcode: '123456',
            name: 'Test Product',
            brand: 'Brand',
            species: 'Salmon',
            weight: 100,
            weight_drained: 50,
            product_code: 'PC1',
            image_url: null,
            validation_status: \Bristolian\Model\TinnedFish\ValidationStatus::NOT_VALIDATED,
            raw_data: null,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable(),
        );
        $repo = new FakeTinnedFishProductRepo([$product]);
        $this->assertSame($product, $repo->getByBarcode('123456'));
        $this->assertCount(1, $repo->getAll());
    }
}

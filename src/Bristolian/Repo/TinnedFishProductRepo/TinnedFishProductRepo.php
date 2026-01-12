<?php

declare(strict_types=1);

namespace Bristolian\Repo\TinnedFishProductRepo;

use Bristolian\Model\TinnedFish\Product;

/**
 * Repository interface for accessing the canonical tinned fish product database.
 */
interface TinnedFishProductRepo
{
    /**
     * Find a product by its barcode.
     *
     * @param string $barcode The EAN/UPC/GTIN barcode
     * @return Product|null The product if found, null otherwise
     */
    public function getByBarcode(string $barcode): ?Product;
}

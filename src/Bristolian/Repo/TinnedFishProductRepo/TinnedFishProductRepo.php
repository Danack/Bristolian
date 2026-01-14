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

    /**
     * Get all products.
     *
     * @return Product[]
     */
    public function getAll(): array;

    /**
     * Save a product to the database.
     * If a product with the same barcode exists, it will be updated.
     *
     * @param Product $product The product to save
     */
    public function save(Product $product): void;
}

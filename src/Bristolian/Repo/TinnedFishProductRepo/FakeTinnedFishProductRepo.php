<?php

declare(strict_types = 1);

namespace Bristolian\Repo\TinnedFishProductRepo;

use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;

/**
 * Fake implementation of TinnedFishProductRepo for testing.
 */
class FakeTinnedFishProductRepo implements TinnedFishProductRepo
{
    /**
     * @var Product[]
     */
    private array $products = [];

    /**
     * @param Product[] $initialProducts
     */
    public function __construct(array $initialProducts = [])
    {
        $this->products = $initialProducts;
    }

    public function getByBarcode(string $barcode): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->barcode === $barcode) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return Product[]
     */
    public function getAll(): array
    {
        return $this->products;
    }

    public function save(Product $product): void
    {
        // Remove existing product with same barcode if it exists
        $this->products = array_filter(
            $this->products,
            fn(Product $p) => $p->barcode !== $product->barcode
        );

        $this->products[] = $product;
    }

    public function updateValidationStatus(string $barcode, ValidationStatus $validationStatus): void
    {
        foreach ($this->products as $index => $product) {
            if ($product->barcode === $barcode) {
                $this->products[$index] = new Product(
                    $product->barcode,
                    $product->name,
                    $product->brand,
                    $product->species,
                    $product->weight,
                    $product->weight_drained,
                    $product->product_code,
                    $product->image_url,
                    $validationStatus,
                    $product->raw_data,
                    $product->created_at,
                    $product->updated_at
                );
                return;
            }
        }
    }
}

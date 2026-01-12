<?php

declare(strict_types=1);

namespace Bristolian\Model\TinnedFish;

use Bristolian\ToArray;

/**
 * Product information for a tinned fish product.
 * Used in API responses for product lookup.
 */
class Product
{
    use ToArray;

    public function __construct(
        public readonly string $barcode,
        public readonly string $name,
        public readonly string $brand,
        public readonly ?string $species,
        public readonly ?float $weight,
        public readonly ?float $weight_drained,
        public readonly ?string $product_code,
        public readonly ?string $image_url,
        public readonly ?array $raw_data = null,
        public readonly ?\DateTimeInterface $created_at = null,
        public readonly ?\DateTimeInterface $updated_at = null
    ) {
    }
}

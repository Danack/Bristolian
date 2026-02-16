<?php

declare(strict_types = 1);

namespace Bristolian\Model\TinnedFish;

use Bristolian\ToArray;

/**
 * Product information for a tinned fish product.
 * Used in API responses for product lookup.
 */
class Product
{
    use ToArray;

    /**
     * @param ?array<string, mixed> $raw_data
     */
    public function __construct(
        public readonly string $barcode,
        public readonly string $name,
        public readonly string $brand,
        public readonly ?string $species,
        public readonly ?float $weight,
        public readonly ?float $weight_drained,
        public readonly ?string $product_code,
        public readonly ?string $image_url,
        public readonly ValidationStatus $validation_status = ValidationStatus::NOT_VALIDATED,
        public readonly ?array $raw_data = null,
        public readonly ?\DateTimeInterface $created_at = null,
        public readonly ?\DateTimeInterface $updated_at = null
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $validationStatus = ValidationStatus::NOT_VALIDATED;
        if (isset($row['validation_status'])) {
            $validationStatus = ValidationStatus::from($row['validation_status']);
        }

        return new self(
            barcode: $row['barcode'],
            name: $row['name'],
            brand: $row['brand'],
            species: $row['species'],
            weight: $row['weight'] !== null ? (float)$row['weight'] : null,
            weight_drained: $row['weight_drained'] !== null ? (float)$row['weight_drained'] : null,
            product_code: $row['product_code'],
            image_url: $row['image_url'],
            validation_status: $validationStatus,
            raw_data: null,
            created_at: new \DateTimeImmutable($row['created_at']),
            updated_at: new \DateTimeImmutable($row['updated_at'])
        );
    }
}

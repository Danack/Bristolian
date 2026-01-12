<?php

declare(strict_types=1);

namespace Bristolian\Repo\TinnedFishProductRepo;

use Bristolian\Database\tinned_fish_product;
use Bristolian\Model\TinnedFish\Product;
use Bristolian\PdoSimple\PdoSimple;

/**
 * PDO-based implementation of TinnedFishProductRepo.
 * Accesses the canonical product database.
 */
class PdoTinnedFishProductRepo implements TinnedFishProductRepo
{
    public function __construct(
        private PdoSimple $pdo_simple
    ) {
    }

    public function getByBarcode(string $barcode): ?Product
    {
        $sql = tinned_fish_product::SELECT;
        $sql .= " WHERE barcode = :barcode";

        $row = $this->pdo_simple->fetchOneAsDataOrNull(
            $sql,
            [':barcode' => $barcode]
        );

        if ($row === null) {
            return null;
        }

        return new Product(
            barcode: $row['barcode'],
            name: $row['name'],
            brand: $row['brand'],
            species: $row['species'],
            weight: $row['weight'] !== null ? (float)$row['weight'] : null,
            weight_drained: $row['weight_drained'] !== null ? (float)$row['weight_drained'] : null,
            product_code: $row['product_code'],
            image_url: $row['image_url'],
            raw_data: null,
            created_at: new \DateTimeImmutable($row['created_at']),
            updated_at: new \DateTimeImmutable($row['updated_at'])
        );
    }
}

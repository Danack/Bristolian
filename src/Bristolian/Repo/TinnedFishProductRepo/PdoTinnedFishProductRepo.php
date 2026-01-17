<?php

declare(strict_types = 1);

namespace Bristolian\Repo\TinnedFishProductRepo;

use Bristolian\Database\tinned_fish_product;
use Bristolian\Model\TinnedFish\Product;
use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\PdoSimple\PdoSimple;
use Ramsey\Uuid\Uuid;

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

        return $this->rowToProduct($row);
    }

    /**
     * @return Product[]
     */
    public function getAll(): array
    {
        $sql = tinned_fish_product::SELECT;
        $sql .= " ORDER BY created_at DESC";

        $rows = $this->pdo_simple->fetchAllAsData($sql, []);

        $products = [];
        foreach ($rows as $row) {
            $products[] = $this->rowToProduct($row);
        }

        return $products;
    }

    public function save(Product $product): void
    {
        $sql = tinned_fish_product::INSERT;
        $sql .= " ON DUPLICATE KEY UPDATE
            name = :name_update,
            brand = :brand_update,
            species = :species_update,
            weight = :weight_update,
            weight_drained = :weight_drained_update,
            product_code = :product_code_update,
            image_url = :image_url_update,
            validation_status = :validation_status_update";

        $uuid = Uuid::uuid7();
        $id = $uuid->toString();

        $params = [
            ':id' => $id,
            ':barcode' => $product->barcode,
            ':name' => $product->name,
            ':brand' => $product->brand,
            ':species' => $product->species,
            ':weight' => $product->weight,
            ':weight_drained' => $product->weight_drained,
            ':product_code' => $product->product_code,
            ':image_url' => $product->image_url,
            ':validation_status' => $product->validation_status->value,
            // Duplicate params for ON DUPLICATE KEY UPDATE
            ':name_update' => $product->name,
            ':brand_update' => $product->brand,
            ':species_update' => $product->species,
            ':weight_update' => $product->weight,
            ':weight_drained_update' => $product->weight_drained,
            ':product_code_update' => $product->product_code,
            ':image_url_update' => $product->image_url,
            ':validation_status_update' => $product->validation_status->value,
        ];

        $this->pdo_simple->execute($sql, $params);
    }

    public function updateValidationStatus(string $barcode, ValidationStatus $validationStatus): void
    {
        $sql = "UPDATE tinned_fish_product 
                SET validation_status = :validation_status 
                WHERE barcode = :barcode 
                LIMIT 1";

        $this->pdo_simple->execute($sql, [
            ':barcode' => $barcode,
            ':validation_status' => $validationStatus->value,
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function rowToProduct(array $row): Product
    {
        $validationStatus = ValidationStatus::NOT_VALIDATED;
        if (isset($row['validation_status'])) {
            $validationStatus = ValidationStatus::from($row['validation_status']);
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
            validation_status: $validationStatus,
            raw_data: null,
            created_at: new \DateTimeImmutable($row['created_at']),
            updated_at: new \DateTimeImmutable($row['updated_at'])
        );
    }
}

<?php

declare(strict_types = 1);

use Bristolian\Model\TinnedFish\Product;

/**
 * Normalize OpenFoodFacts API response into a Product object.
 *
 * @param string $barcode The barcode used for lookup
 * @param array<string, mixed> $rawData The raw API response from OpenFoodFacts
 * @return Product The normalized product
 */
function normalizeOpenFoodFactsData(string $barcode, array $rawData): Product
{
    $product = $rawData['product'] ?? [];

    $name = $product['product_name'] ?? $product['product_name_en'] ?? 'Unknown';
    $brand = $product['brands'] ?? 'Unknown';
    $imageUrl = $product['image_url'] ?? $product['image_front_url'] ?? null;

    // Parse weight information
    $quantity = $product['quantity'] ?? '';
    [$weight, $weightDrained] = parseTinnedFishWeight($quantity);

    // Try to extract fish species from categories or product name
    $species = extractTinnedFishSpecies($product);

    return new Product(
        barcode: $barcode,
        name: $name,
        brand: $brand,
        species: $species,
        weight: $weight,
        weight_drained: $weightDrained,
        product_code: null, // External products don't have internal product codes
        image_url: $imageUrl,
        raw_data: $rawData
    );
}

/**
 * Parse weight information from a quantity string.
 * Handles various formats like:
 * - "125 g"
 * - "125g"
 * - "125 g (égoutté: 90g)"
 * - "125 g (drained: 90g)"
 * - "125 g (90g drained)"
 *
 * @param string $quantity The quantity string from the API
 * @return array{float|null, float|null} [weight, weight_drained]
 */
function parseTinnedFishWeight(string $quantity): array
{
    $weight = null;
    $weightDrained = null;

    if (empty($quantity)) {
        return [$weight, $weightDrained];
    }

    // Try to extract main weight (e.g., "125 g" or "125g")
    if (preg_match('/(\d+(?:[.,]\d+)?)\s*g(?:\b|$)/i', $quantity, $matches)) {
        $weight = (float)str_replace(',', '.', $matches[1]);
    }

    // Try to extract drained weight
    // Patterns: "égoutté: 90g", "drained: 90g", "(90g drained)", "net wt 90g"
    $drainedPatterns = [
        '/[ée]goutt[ée]\s*:?\s*(\d+(?:[.,]\d+)?)\s*g/iu',
        '/drained\s*:?\s*(\d+(?:[.,]\d+)?)\s*g/i',
        '/\((\d+(?:[.,]\d+)?)\s*g\s*drained\)/i',
        '/net\s*(?:wt|weight)\s*:?\s*(\d+(?:[.,]\d+)?)\s*g/i',
    ];

    foreach ($drainedPatterns as $pattern) {
        if (preg_match($pattern, $quantity, $matches)) {
            $weightDrained = (float)str_replace(',', '.', $matches[1]);
            break;
        }
    }

    return [$weight, $weightDrained];
}

/**
 * Try to extract fish species from product data.
 *
 * @param array<string, mixed> $product The product data from OpenFoodFacts
 * @return string|null The fish species if found
 */
function extractTinnedFishSpecies(array $product): ?string
{
    $commonSpecies = [
        'sardine' => 'Sardines',
        'sardines' => 'Sardines',
        'tuna' => 'Tuna',
        'thon' => 'Tuna',
        'salmon' => 'Salmon',
        'saumon' => 'Salmon',
        'mackerel' => 'Mackerel',
        'maquereau' => 'Mackerel',
        'anchovy' => 'Anchovies',
        'anchois' => 'Anchovies',
        'anchovies' => 'Anchovies',
        'herring' => 'Herring',
        'hareng' => 'Herring',
        'cod' => 'Cod',
        'morue' => 'Cod',
        'trout' => 'Trout',
        'truite' => 'Trout',
        'sprat' => 'Sprats',
        'sprats' => 'Sprats',
        'pilchard' => 'Pilchards',
        'pilchards' => 'Pilchards',
    ];

    // Check product name
    $productName = strtolower($product['product_name'] ?? '');
    foreach ($commonSpecies as $keyword => $species) {
        if (str_contains($productName, $keyword)) {
            return $species;
        }
    }

    // Check categories
    $categories = strtolower($product['categories'] ?? '');
    foreach ($commonSpecies as $keyword => $species) {
        if (str_contains($categories, $keyword)) {
            return $species;
        }
    }

    return null;
}

<?php

declare(strict_types=1);

namespace Bristolian\Service\TinnedFish;

use Bristolian\Exception\BristolianException;

/**
 * Fetches product data from the OpenFoodFacts API.
 * @see https://world.openfoodfacts.org/
 */
class OpenFoodFactsFetcher
{
    private const BASE_URL = 'https://world.openfoodfacts.org/api/v0/product/';

    /**
     * Fetch product data by barcode from OpenFoodFacts API.
     *
     * @param string $barcode The EAN/UPC/GTIN barcode
     * @return array<string, mixed>|null The raw API response data, or null if product not found
     * @throws OpenFoodFactsApiException If the API request fails
     */
    public function fetchProduct(string $barcode): ?array
    {
        $url = self::BASE_URL . urlencode($barcode) . '.json';

        [$statusCode, $body, $headers] = fetchUri($url, 'GET');

        if ($statusCode === 404) {
            return null;
        }

        if ($statusCode !== 200) {
            throw new OpenFoodFactsApiException(
                "OpenFoodFacts API returned status code: $statusCode"
            );
        }

        try {
            $data = json_decode_safe($body);
        } catch (\Throwable $e) {
            throw new OpenFoodFactsApiException(
                "Failed to parse OpenFoodFacts API response: " . $e->getMessage()
            );
        }

        // OpenFoodFacts returns status=0 when product not found
        if (!isset($data['status']) || $data['status'] === 0) {
            return null;
        }

        return $data;
    }
}

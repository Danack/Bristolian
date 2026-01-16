<?php

declare(strict_types=1);

namespace Bristolian\ApiController;

use Bristolian\Model\TinnedFish\Copyright;
use Bristolian\Parameters\TinnedFish\BarcodeLookupParams;
use Bristolian\Repo\TinnedFishProductRepo\TinnedFishProductRepo;
use Bristolian\Response\TinnedFish\ExternalApiErrorResponse;
use Bristolian\Response\TinnedFish\GetAllProductsResponse;
use Bristolian\Response\TinnedFish\InvalidBarcodeResponse;
use Bristolian\Response\TinnedFish\ProductLookupResponse;
use Bristolian\Response\TinnedFish\ProductNotFoundResponse;
use Bristolian\Service\TinnedFish\OpenFoodFactsApiException;
use Bristolian\Service\TinnedFish\OpenFoodFactsFetcher;
use SlimDispatcher\Response\StubResponse;

use function normalizeOpenFoodFactsData;

/**
 * API controller for Tinned Fish Diary product lookup.
 */
class TinnedFish
{
    /**
     * Lookup product information by barcode.
     *
     * First checks the canonical database, then optionally fetches from
     * external APIs (OpenFoodFacts) if not found.
     *
     * @param BarcodeLookupParams $params Query parameters (fetch_external)
     * @param string $barcode The barcode from URL path
     * @param TinnedFishProductRepo $productRepo Repository for canonical database
     * @param OpenFoodFactsFetcher $fetcher External API fetcher
     * @return StubResponse
     */
    public function getProductByBarcode(
        BarcodeLookupParams $params,
        string $barcode,
        TinnedFishProductRepo $productRepo,
        OpenFoodFactsFetcher $fetcher
    ): StubResponse {
        // Validate barcode format (8-13 digits)
        if (!$this->isValidBarcode($barcode)) {
            return new InvalidBarcodeResponse($barcode);
        }

        // First, check the canonical database
        $product = $productRepo->getByBarcode($barcode);

        if ($product !== null) {
            // Found in canonical database
            return new ProductLookupResponse(
                source: 'canonical',
                product: $product,
                copyright: null
            );
        }

        // Not found in canonical database
        // If fetch_external is false, return 404
        if ($params->fetch_external === false) {
            return new ProductNotFoundResponse($barcode);
        }

        // Try to fetch from external API
        try {
            $rawData = $fetcher->fetchProduct($barcode);
        } catch (OpenFoodFactsApiException $e) {
            return new ExternalApiErrorResponse($barcode, $e->getMessage());
        }

        if ($rawData === null) {
            // Not found in external API either
            return new ProductNotFoundResponse($barcode);
        }

        // Normalize the external data
        $product = normalizeOpenFoodFactsData($barcode, $rawData);

        // Cache the product in the canonical database for future lookups
        $productRepo->save($product);

        // Return with copyright attribution
        return new ProductLookupResponse(
            source: 'external',
            product: $product,
            copyright: Copyright::openFoodFacts()
        );
    }

    /**
     * Get all products from the canonical database.
     *
     * @param TinnedFishProductRepo $productRepo Repository for canonical database
     * @return StubResponse
     */
    public function getAllProducts(
        TinnedFishProductRepo $productRepo
    ): StubResponse {
        $products = $productRepo->getAll();
        
        return new GetAllProductsResponse($products);
    }

    /**
     * Validate barcode format.
     * Must be 8-13 digits (EAN/UPC/GTIN format).
     */
    private function isValidBarcode(string $barcode): bool
    {
        return preg_match('/^\d{8,13}$/', $barcode) === 1;
    }
}

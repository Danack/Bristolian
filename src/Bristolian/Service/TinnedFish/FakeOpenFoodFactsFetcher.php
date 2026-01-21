<?php

declare(strict_types = 1);

namespace Bristolian\Service\TinnedFish;

/**
 * Fake implementation of OpenFoodFactsFetcher for testing.
 */
class FakeOpenFoodFactsFetcher extends OpenFoodFactsFetcher
{
    /**
     * @var array<string, array<string, mixed>|null>
     * Maps barcode to response data (null means product not found)
     */
    private array $responses = [];

    /**
     * @var array<string, OpenFoodFactsApiException>
     * Maps barcode to exception to throw
     */
    private array $exceptions = [];

    /**
     * Set the response for a specific barcode.
     *
     * @param string $barcode
     * @param array<string, mixed>|null $data Response data, or null for not found
     */
    public function setResponse(string $barcode, ?array $data): void
    {
        $this->responses[$barcode] = $data;
    }

    /**
     * Set an exception to throw for a specific barcode.
     *
     * @param string $barcode
     * @param OpenFoodFactsApiException $exception
     */
    public function setException(string $barcode, OpenFoodFactsApiException $exception): void
    {
        $this->exceptions[$barcode] = $exception;
    }

    public function fetchProduct(string $barcode): ?array
    {
        // Check if we should throw an exception
        if (isset($this->exceptions[$barcode])) {
            throw $this->exceptions[$barcode];
        }

        // Return the configured response, or null if not set
        return $this->responses[$barcode] ?? null;
    }
}

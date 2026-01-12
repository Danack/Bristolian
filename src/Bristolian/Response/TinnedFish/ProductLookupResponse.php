<?php

declare(strict_types=1);

namespace Bristolian\Response\TinnedFish;

use Bristolian\Model\TinnedFish\Copyright;
use Bristolian\Model\TinnedFish\Product;
use SlimDispatcher\Response\StubResponse;

/**
 * Successful product lookup response.
 */
class ProductLookupResponse implements StubResponse
{
    private string $body;

    public function __construct(
        string $source,
        Product $product,
        ?Copyright $copyright = null
    ) {
        [$error, $productData] = \convertToValue($product);
        if ($error !== null) {
            throw new \RuntimeException("Failed to convert product to value: $error");
        }

        $copyrightData = null;
        if ($copyright !== null) {
            [$error, $copyrightData] = \convertToValue($copyright);
            if ($error !== null) {
                throw new \RuntimeException("Failed to convert copyright to value: $error");
            }
        }

        $response = [
            'success' => true,
            'source' => $source,
            'product' => $productData,
            'copyright' => $copyrightData,
        ];

        $this->body = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 200;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

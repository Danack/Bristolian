<?php

declare(strict_types=1);

namespace Bristolian\Response\TinnedFish;

use Bristolian\Model\TinnedFish\ProductError;
use SlimDispatcher\Response\StubResponse;

/**
 * 404 response when product is not found.
 */
class ProductNotFoundResponse implements StubResponse
{
    private string $body;

    public function __construct(string $barcode)
    {
        $error = ProductError::productNotFound($barcode);
        [$err, $errorData] = \convertToValue($error);

        $response = [
            'success' => false,
            'error' => $errorData,
        ];

        $this->body = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 404;
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

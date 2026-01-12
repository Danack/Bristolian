<?php

declare(strict_types=1);

namespace Bristolian\Response\TinnedFish;

use Bristolian\Model\TinnedFish\ProductError;
use SlimDispatcher\Response\StubResponse;

/**
 * 400 response for invalid barcode format.
 */
class InvalidBarcodeResponse implements StubResponse
{
    private string $body;

    public function __construct(string $barcode)
    {
        $error = ProductError::invalidBarcode($barcode);
        [$err, $errorData] = \convertToValue($error);

        $response = [
            'success' => false,
            'error' => $errorData,
        ];

        $this->body = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 400;
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

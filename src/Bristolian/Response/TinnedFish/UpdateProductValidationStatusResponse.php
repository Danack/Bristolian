<?php

declare(strict_types = 1);

namespace Bristolian\Response\TinnedFish;

use Bristolian\Model\TinnedFish\ValidationStatus;
use SlimDispatcher\Response\StubResponse;

/**
 * Response for updating product validation status.
 */
class UpdateProductValidationStatusResponse implements StubResponse
{
    private string $body;

    public function __construct(
        string $barcode,
        ValidationStatus $validation_status
    ) {
        $response = [
            'success' => true,
            'barcode' => $barcode,
            'validation_status' => $validation_status->value,
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

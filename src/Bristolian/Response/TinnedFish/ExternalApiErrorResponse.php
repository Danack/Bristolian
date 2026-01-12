<?php

declare(strict_types=1);

namespace Bristolian\Response\TinnedFish;

use Bristolian\Model\TinnedFish\ProductError;
use SlimDispatcher\Response\StubResponse;

/**
 * 502 response when external API call fails.
 */
class ExternalApiErrorResponse implements StubResponse
{
    private string $body;

    public function __construct(string $barcode, string $details)
    {
        $error = ProductError::externalApiError($barcode, $details);
        [$err, $errorData] = \convertToValue($error);

        $response = [
            'success' => false,
            'error' => $errorData,
        ];

        $this->body = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 502;
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

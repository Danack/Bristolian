<?php

declare(strict_types = 1);

namespace Bristolian\Response\TinnedFish;

use SlimDispatcher\Response\StubResponse;

/**
 * Response for generating an API token.
 */
class GenerateApiTokenResponse implements StubResponse
{
    private string $body;

    public function __construct(
        string $token,
        string $name,
        string $qr_code_url,
        \DateTimeInterface $created_at
    ) {
        $response = [
            'success' => true,
            'token' => $token,
            'name' => $name,
            'qr_code_url' => $qr_code_url,
            'created_at' => $created_at->format(\Bristolian\App::DATE_TIME_FORMAT),
        ];

        $this->body = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
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

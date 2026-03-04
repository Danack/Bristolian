<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

/**
 * 400 response when fetching a transcript (e.g. from YouTube) fails.
 */
class FetchTranscriptErrorResponse implements StubResponse
{
    private string $body;

    public function __construct(string $errorMessage)
    {
        $response = [
            'result' => 'error',
            'error' => $errorMessage,
        ];
        $this->body = json_encode_safe($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
            'Content-Type' => 'application/json',
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

/**
 * Response for GET room video transcripts list (result + data.transcripts).
 */
class GetTranscriptsResponse implements StubResponse
{
    private string $body;

    /**
     * @param list<array<string, mixed>> $transcripts Each item: id, transcript_number, language, created_at
     */
    public function __construct(array $transcripts)
    {
        $response = [
            'result' => 'success',
            'data' => ['transcripts' => $transcripts],
        ];
        $this->body = json_encode_safe($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
            'Content-Type' => 'application/json',
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

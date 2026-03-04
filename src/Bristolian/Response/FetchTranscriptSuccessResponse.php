<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

/**
 * Response for POST fetch transcript (result + data.transcript_id, transcript_number).
 */
class FetchTranscriptSuccessResponse implements StubResponse
{
    private string $body;

    public function __construct(string $transcriptId, ?int $transcriptNumber)
    {
        $response = [
            'result' => 'success',
            'data' => [
                'transcript_id' => $transcriptId,
                'transcript_number' => $transcriptNumber,
            ],
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

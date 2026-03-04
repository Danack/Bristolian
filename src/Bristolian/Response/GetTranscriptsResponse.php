<?php

declare(strict_types=1);

namespace Bristolian\Response;

use Bristolian\Model\Types\RoomVideoTranscriptList;
use SlimDispatcher\Response\StubResponse;

/**
 * Response for GET room video transcripts list (result + data.transcripts).
 * Converts RoomVideoTranscriptList to JSON-serializable form via convertToValueSafe.
 */
class GetTranscriptsResponse implements StubResponse
{
    private string $body;

    public function __construct(RoomVideoTranscriptList $transcriptList)
    {
        $convertedTranscripts = \convertToValueSafe($transcriptList->transcripts);
        $response = [
            'result' => 'success',
            'data' => ['transcripts' => $convertedTranscripts],
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

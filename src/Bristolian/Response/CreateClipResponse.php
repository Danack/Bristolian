<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

/**
 * Response for POST /api/rooms/{room_id}/videos/clips (create clip).
 */
class CreateClipResponse implements StubResponse
{
    private string $body;

    public function __construct(string $room_video_id)
    {
        $response = [
            'result' => 'success',
            'data' => [
                'room_video_id' => $room_video_id,
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

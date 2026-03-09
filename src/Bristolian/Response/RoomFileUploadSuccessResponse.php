<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class RoomFileUploadSuccessResponse implements StubResponse
{
    private string $body;

    public function __construct(string $fileId)
    {
        $response = [
            'result' => 'success',
            'file_id' => $fileId,
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

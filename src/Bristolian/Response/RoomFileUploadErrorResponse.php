<?php

declare(strict_types=1);

namespace Bristolian\Response;

use Bristolian\Service\RoomFileStorage\UploadError;
use SlimDispatcher\Response\StubResponse;

class RoomFileUploadErrorResponse implements StubResponse
{
    private string $body;

    public function __construct(UploadError $error)
    {
        $data = [
            'result' => 'error',
            'error' => $error->error_message,
        ];
        $this->body = json_encode_safe($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

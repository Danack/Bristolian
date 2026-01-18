<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class UploadAvatarResponse implements StubResponse
{
    private string $body;

    public function __construct(string $avatar_image_id)
    {
        $payload = [
            'success' => true,
            'avatar_image_id' => $avatar_image_id,
        ];

        $this->body = json_encode_safe($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

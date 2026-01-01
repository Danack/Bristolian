<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

/**
 * Keeps legacy shape: plain object with user fields.
 */
class GetUserInfoResponse implements StubResponse
{
    private string $body;

    /**
     * @param array<string, mixed> $userInfo
     */
    public function __construct(array $userInfo)
    {
        $this->body = json_encode_safe($userInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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


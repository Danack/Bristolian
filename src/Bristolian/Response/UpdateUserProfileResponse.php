<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

/**
 * Keeps legacy shape: { success: true, profile: {...} }
 */
class UpdateUserProfileResponse implements StubResponse
{
    private string $body;

    /**
     * @param array<string, mixed> $profile
     */
    public function __construct(array $profile)
    {
        $payload = [
            'success' => true,
            'profile' => $profile,
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

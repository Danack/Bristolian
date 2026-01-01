<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class GetMemeTagsResponse implements StubResponse
{
    private string $body;

    /**
     * @param array<int, mixed> $tags
     */
    public function __construct(array $tags)
    {
        $payload = [
            'result' => 'success',
            'data' => [
                'meme_tags' => $tags,
            ],
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


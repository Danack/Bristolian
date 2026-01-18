<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use Bristolian\Model\Generated\MemeText;
use SlimDispatcher\Response\StubResponse;

class GetMemeTextResponse implements StubResponse
{
    private string $body;

    /**
     * @param MemeText|null $meme_text
     */
    public function __construct(?MemeText $meme_text)
    {
        $payload = [
            'result' => 'success',
            'data' => [
                'meme_text' => $meme_text === null ? null : [
                    'id' => $meme_text->id,
                    'text' => $meme_text->text,
                    'meme_id' => $meme_text->meme_id,
                    'created_at' => $meme_text->created_at->format('c'),
                ],
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

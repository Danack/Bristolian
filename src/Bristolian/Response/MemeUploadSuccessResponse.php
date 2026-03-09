<?php

declare(strict_types=1);

namespace Bristolian\Response;

use Bristolian\Service\MemeStorageProcessor\ObjectStoredMeme;
use SlimDispatcher\Response\StubResponse;

class MemeUploadSuccessResponse implements StubResponse
{
    private string $body;

    public function __construct(ObjectStoredMeme $meme)
    {
        $response = [
            'result' => 'success',
            'next' => 'actually upload to file_server.',
            'meme_id' => $meme->meme_id,
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

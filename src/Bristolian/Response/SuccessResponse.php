<?php

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class SuccessResponse implements StubResponse
{
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
            'Content-Type' => 'application/json'
        ];
    }

    public function getBody(): string
    {
        $response_ok = [
            'result' => 'success',
        ];

        return json_encode_safe($response_ok, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

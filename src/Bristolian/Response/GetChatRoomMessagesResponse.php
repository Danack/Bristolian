<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class GetChatRoomMessagesResponse implements StubResponse
{
    private string $body;

    /**
     * @param array<int, array<string, mixed>> $messages
     */
    public function __construct(array $messages)
    {
        $converted_messages = \convertToValueSafe($messages);

        $response_ok = [
            'result' => 'success',
            'data' => [
                'messages' => $converted_messages,
            ],
        ];

        $this->body = json_encode_safe($response_ok, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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

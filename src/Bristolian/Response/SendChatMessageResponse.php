<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use Bristolian\Exception\DataEncodingException;
use Bristolian\Model\Chat\UserChatMessage;
use SlimDispatcher\Response\StubResponse;

class SendChatMessageResponse implements StubResponse
{
    private string $body;

    public function __construct(UserChatMessage $chatMessage)
    {
        [$error, $converted_message] = \convertToValue($chatMessage);
        if ($error !== null) {
            throw new DataEncodingException("Could not convert chat message to a value. ", $error);
        }

        $response_ok = [
            'result' => 'success',
            'data' => [
                'chat_message' => $converted_message,
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


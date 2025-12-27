<?php

declare(strict_types=1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class EndpointAccessedViaGetResponse implements StubResponse
{
    private const DEFAULT_MESSAGE = 'This endpoint expects a POST request. Please send a POST request instead of GET.';

    private const DELETE_MESSAGE = 'This endpoint expects a DELETE request. Please send a DELETE request instead of GET.';


    private string $message;

    public function __construct(string $message = self::DEFAULT_MESSAGE)
    {
        $this->message = $message;
    }

    public static function forDelete(): self
    {
        return new self(self::DELETE_MESSAGE);
    }

    public function getStatus(): int
    {
        // 405 Method Not Allowed
        return 405;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'text/plain',
            'Allow' => 'POST',
        ];
    }

    public function getBody(): string
    {
        return $this->message;
    }
}


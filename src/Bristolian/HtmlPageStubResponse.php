<?php

declare(strict_types = 1);

namespace Bristolian;

use SlimDispatcher\Response\StubResponse;

class HtmlPageStubResponse implements StubResponse
{
    function __construct(
        private int $status,
        private string $body,
        private array $headers
    ) {
        $this->headers['Content-Type'] = 'text/html; charset=UTF-8';
    }

    public static function createErrorPage(string $errorPageHtml)
    {
        return new self(
            501,
            $errorPageHtml,
            ['ContentType: text/html']
        );
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}

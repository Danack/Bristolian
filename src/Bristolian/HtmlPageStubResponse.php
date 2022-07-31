<?php

declare(strict_types = 1);

namespace Bristolian;

use SlimAuryn\Response\StubResponse;

class HtmlPageStubResponse implements StubResponse
{
    function __construct(
        private int $status,
        private string $body,
        private array $headers
    ) {

    }

    public static function createErrorPage(string $errorPageHtml)
    {

    }

    public function getStatus(): int
    {
        throw new \Exception("getStatus not implemented yet.");
    }

    public function getBody(): string
    {
        throw new \Exception("getBody not implemented yet.");
    }

    public function getHeaders(): array
    {
        throw new \Exception("getHeaders not implemented yet.");
    }
}

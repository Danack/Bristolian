<?php

declare(strict_types = 1);

namespace Bristolian\Service\BccTroFetcher;

/**
 * Test double: fixed page HTML or throws.
 */
final class FakeBccTroFetcher implements BccTroFetcher
{
    public function __construct(
        private string $pageHtml = '<html></html>',
        private \Throwable|null $throwable = null
    ) {
    }

    public function fetchPage(): string
    {
        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        return $this->pageHtml;
    }
}

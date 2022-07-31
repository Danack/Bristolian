<?php

declare(strict_types = 1);

namespace Bristolian\ExternalMarkdownRenderer;

interface ExternalMarkdownRenderer
{
    public function renderUrl(string $url): string;
}

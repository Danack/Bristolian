<?php

declare(strict_types = 1);

namespace Bristolian\MarkdownRenderer;

interface MarkdownRenderer
{
    public function render(string $markdown): string;

    public function renderFile(string $filepath): string;
}

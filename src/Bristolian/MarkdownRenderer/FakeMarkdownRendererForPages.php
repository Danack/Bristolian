<?php

declare(strict_types = 1);

namespace Bristolian\MarkdownRenderer;

use Bristolian\MarkdownRenderer\MarkdownRenderer;

/**
 * Fake MarkdownRenderer for tests when doc files are not present (e.g. complaints/, questions/).
 * @coversNothing
 */
final class FakeMarkdownRendererForPages implements MarkdownRenderer
{
    public function render(string $markdown): string
    {
        return $markdown;
    }

    public function renderFile(string $filepath): string
    {
        return '<p>Rendered content from ' . basename($filepath) . '</p>';
    }
}
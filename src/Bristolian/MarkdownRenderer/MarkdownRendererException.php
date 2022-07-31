<?php

declare(strict_types = 1);

namespace Bristolian\MarkdownRenderer;

class MarkdownRendererException extends \Exception
{
    const FILE_NOT_FOUND_MESSAGE = "Failed to read file '%s' cannot render markdown.";

    public static function fileNotFound(string $filename)
    {
        $message = sprintf(self::FILE_NOT_FOUND_MESSAGE, $filename);
        return new self($message);
    }
}

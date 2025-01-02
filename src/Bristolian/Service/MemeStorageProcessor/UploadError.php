<?php

namespace Bristolian\Service\MemeStorageProcessor;

class UploadError
{
    protected function __construct(public readonly string $error_message)
    {
    }

    public static function uploadedFileUnreadable(): self
    {
        return new self("Failed to read temp uploaded file.");
    }

    public static function unsupportedFileType(): self
    {
        return new self("File type is unsupported.");
    }
}

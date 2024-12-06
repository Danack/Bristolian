<?php

namespace Bristolian\Service\FileStorageProcessor;

class UploadError
{
    protected function __construct(public readonly string $error_message)
    {
    }

    public static function uploadedFileUnreadable()
    {
        return new self("Failed to read temp uploaded file.");
    }

    public static function unsupportedFileType()
    {
        return new self("File type is unsupported.");
    }
}

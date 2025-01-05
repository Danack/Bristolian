<?php

namespace Bristolian\Service\MemeStorageProcessor;

class UploadError
{
    const UNREADABLE_FILE_MESSAGE = "Failed to read temp uploaded file.";
    const UNSUPPORTED_FILE_TYPE = "File type is unsupported.";

    protected function __construct(public readonly string $error_message)
    {
    }

    public static function uploadedFileUnreadable(): self
    {
        return new self(self::UNREADABLE_FILE_MESSAGE);
    }

    public static function unsupportedFileType(): self
    {
        return new self(self::UNSUPPORTED_FILE_TYPE);
    }
}

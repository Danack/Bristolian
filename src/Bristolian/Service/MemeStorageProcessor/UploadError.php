<?php

namespace Bristolian\Service\MemeStorageProcessor;

class UploadError
{
    const UNREADABLE_FILE_MESSAGE = "Failed to read temp uploaded file.";
    const UNSUPPORTED_FILE_TYPE = "File type is unsupported.";
    
    public const DUPLICATE_FILENAME = 'DUPLICATE_FILENAME';

    /**
     * @param array<string, mixed>|null $error_data
     */
    protected function __construct(
        public readonly string $error_message,
        public readonly ?string $error_code = null,
        public readonly ?array $error_data = null
    ) {
    }

    public static function uploadedFileUnreadable(): self
    {
        return new self(self::UNREADABLE_FILE_MESSAGE);
    }

    public static function unsupportedFileType(): self
    {
        return new self(self::UNSUPPORTED_FILE_TYPE);
    }

    public static function duplicateOriginalFilename(string $filename): self
    {
        return new self(
            "A file with the name '$filename' has already been uploaded. Please rename the file and try again.",
            self::DUPLICATE_FILENAME,
            ['filename' => $filename]
        );
    }
}

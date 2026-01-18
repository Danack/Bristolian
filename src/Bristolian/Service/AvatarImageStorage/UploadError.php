<?php

namespace Bristolian\Service\AvatarImageStorage;

class UploadError
{
    private function __construct(
        public readonly string $error_message
    ) {
    }

    public static function uploadedFileUnreadable(): self
    {
        return new self("Uploaded file is not readable");
    }

    public static function extensionNotAllowed(string $extension): self
    {
        return new self("File extension '$extension' is not allowed");
    }

    public static function imageTooSmall(int $width, int $height, int $min_size): self
    {
        return new self("Image is too small ({$width}x{$height}). Must be at least {$min_size}x{$min_size} pixels");
    }
}

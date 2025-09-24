<?php

namespace Bristolian\Service\BristolStairImageStorage;

class ObjectStoredFileInfo
{
    public function __construct(
        public readonly string $normalized_filename,
        public readonly string $fileStorageId
    ) {
    }
}

<?php

namespace Bristolian\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;

class LocalCacheFilesystem extends Filesystem
{
    public function __construct(
        FilesystemAdapter $adapter,
        private string $rootLocation,
    ) {
        parent::__construct($adapter);
    }

    public function getFullPath(): string
    {
        return $this->rootLocation;
    }
}

<?php

declare(strict_types=1);

namespace BristolianTest\Service\MemeFileLocalCache;

use Bristolian\Filesystem\MemeFilesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Meme filesystem that always throws the given exception from readStream (cache miss path).
 */
final class MemeFilesystemThrowsOnReadStream extends MemeFilesystem
{
    public function __construct(
        string $directory,
        private \Throwable $throwOnReadStream,
    ) {
        parent::__construct(new LocalFilesystemAdapter($directory));
    }

    public function readStream(string $path)
    {
        throw $this->throwOnReadStream;
    }
}

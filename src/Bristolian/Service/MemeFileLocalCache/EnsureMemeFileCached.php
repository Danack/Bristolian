<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeFileLocalCache;

use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Service\CliOutput\CliOutput;

/**
 * Copies meme file from object store (meme filesystem) into local cache for OCR.
 * On read failure, writes diagnostics via CliOutput and may mark meme deleted (S3 404).
 */
interface EnsureMemeFileCached
{
    public function ensureMemeFileCached(
        LocalCacheFilesystem $localCacheFilesystem,
        MemeFilesystem $memeFilesystem,
        string $normalizedName,
        string $memeId,
        MemeStorageRepo $memeStorageRepo,
        CliOutput $cliOutput,
    ): EnsureMemeFileCachedResult;
}

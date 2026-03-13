<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeFileLocalCache;

use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Service\CliOutput\CliOutput;

/**
 * Test double: no filesystem access; fixed success or failure.
 */
final class FakeEnsureMemeFileCached implements EnsureMemeFileCached
{
    public function __construct(
        private bool $succeed = true,
        private string $failureDebugInfo = 'fake cache failure',
    ) {
    }

    public function ensureMemeFileCached(
        LocalCacheFilesystem $localCacheFilesystem,
        MemeFilesystem $memeFilesystem,
        string $normalizedName,
        string $memeId,
        MemeStorageRepo $memeStorageRepo,
        CliOutput $cliOutput,
    ): EnsureMemeFileCachedResult {
        if ($this->succeed) {
            return EnsureMemeFileCachedResult::success();
        }

        $cliOutput->write("Failed to download file:\n");
        $cliOutput->write("  " . $this->failureDebugInfo . "\n");
        $cliOutput->write("\n");

        return EnsureMemeFileCachedResult::failure(
            "Failed to download file: " . $this->failureDebugInfo
        );
    }
}

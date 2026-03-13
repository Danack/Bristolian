<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeFileLocalCache;

use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Service\CliOutput\CliOutput;

final class FlysystemEnsureMemeFileCached implements EnsureMemeFileCached
{
    public function ensureMemeFileCached(
        LocalCacheFilesystem $localCacheFilesystem,
        MemeFilesystem $memeFilesystem,
        string $normalizedName,
        string $memeId,
        MemeStorageRepo $memeStorageRepo,
        CliOutput $cliOutput,
    ): EnsureMemeFileCachedResult {
        try {
            \ensureFileCachedFromStream(
                $localCacheFilesystem,
                $memeFilesystem,
                $normalizedName
            );
        }
        catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
            $cliOutput->write("Failed to download file:\n");
            $cliOutput->write("  " . $unableToReadFile->getMessage());
            $cliOutput->write("\n");
            $previous = $unableToReadFile->getPrevious();

            if ($previous !== null) {
                if ($previous instanceof \Aws\S3\Exception\S3Exception) {
                    if ($previous->getStatusCode() === 404) {
                        $cliOutput->write("Meme file not found in storage, marking as deleted.\n");
                        $memeStorageRepo->markAsDeleted($memeId);
                    }
                }
                else {
                    $cliOutput->write("Previous type: " . get_class($previous) . "\n");
                    $cliOutput->write("Previous: \n" . $previous->getMessage());
                    $cliOutput->write("\n");
                }
            }
            $cliOutput->write("\n");

            return EnsureMemeFileCachedResult::failure(
                "Failed to download file: " . $unableToReadFile->getMessage()
            );
        }

        return EnsureMemeFileCachedResult::success();
    }
}

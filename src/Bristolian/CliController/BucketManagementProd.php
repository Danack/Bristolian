<?php

declare(strict_types=1);

namespace Bristolian\CliController;

use Bristolian\Service\CliOutput\CliOutput;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToListContents;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use function Safe\set_time_limit;

/**
 * CLI helpers for archiving Scaleway production object-storage buckets to local disk.
 */
class BucketManagementProd
{
    public const MODE_DRY_RUN = 'dry-run';

    public const MODE_DOWNLOAD = 'download';

    /**
     * Hard-coded production bucket names. Never derived from Config::isProductionEnv().
     *
     * @var list<string>
     */
    public const PRODUCTION_BUCKET_NAMES = [
        'bristolian-memes',
        'bristolian-stairs-images',
        'bristolian-avatar-images',
        'bristolian-user-documents',
        'bristolian-room-files',
    ];

    public function __construct(
        private CliOutput $cliOutput
    ) {
    }

    /**
     * Incrementally archive all known production buckets into archive/<bucket>/.
     *
     * @param string $mode Either 'dry-run' (default) or 'download'
     */
    public function archive(string $mode = self::MODE_DRY_RUN): void
    {
        set_time_limit(0);

        $download = $this->resolveDownloadFlag($mode);

        if ($download === true) {
            $this->cliOutput->write("Mode: download — missing or size-mismatched files will be written to archive/.\n");
        }
        else {
            $this->cliOutput->write("Mode: dry-run — listing files that would be downloaded or skipped.\n");
            $this->cliOutput->write("Re-run with mode 'download' to copy them.\n");
        }

        $this->cliOutput->write("\n");

        try {
            $localFilesystem = createArchiveLocalFilesystem();
        }
        catch (\RuntimeException $exception) {
            $this->cliOutput->write($exception->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        foreach (self::PRODUCTION_BUCKET_NAMES as $bucketName) {
            $remoteFilesystem = createProductionOnlyObjectFilesystem($bucketName);
            $this->archiveBucket($bucketName, $remoteFilesystem, $localFilesystem, $download);
        }
    }

    /**
     * Archive one remote filesystem into archive/{bucketName}/... (used by archive() and tests).
     */
    public function archiveBucket(
        string $bucketName,
        FilesystemOperator $remoteFilesystem,
        FilesystemOperator $localFilesystem,
        bool $download
    ): void {
        $this->cliOutput->write("Bucket: {$bucketName}\n");

        $downloadedCount = 0;
        $skippedCount = 0;
        $wouldDownloadCount = 0;

        try {
            $listing = $remoteFilesystem->listContents('', true);

            foreach ($listing as $item) {
                if ($item instanceof FileAttributes !== true) {
                    continue;
                }

                $remotePath = $item->path();
                $localPath = $bucketName . '/' . $remotePath;
                $remoteSize = $item->fileSize();

                $shouldDownload = $this->shouldDownload(
                    $localFilesystem,
                    $localPath,
                    $remoteSize
                );

                if ($shouldDownload === false) {
                    $skippedCount++;
                    $this->cliOutput->write("  skip (exists): {$localPath}\n");
                    continue;
                }

                $existsLocally = $localFilesystem->fileExists($localPath);
                $reason = $existsLocally === true
                    ? 'would replace (size mismatch)'
                    : 'would download';

                if ($download !== true) {
                    $wouldDownloadCount++;
                    $this->cliOutput->write("  {$reason}: {$localPath}\n");
                    continue;
                }

                try {
                    $stream = $remoteFilesystem->readStream($remotePath);
                    $localFilesystem->writeStream($localPath, $stream);
                    $downloadedCount++;
                    $action = $existsLocally === true ? 'replaced' : 'downloaded';
                    $this->cliOutput->write("  {$action}: {$localPath}\n");
                }
                catch (UnableToReadFile | UnableToWriteFile $exception) {
                    $this->cliOutput->write("  FAILED: {$localPath}\n");
                    $this->cliOutput->write("  " . $exception->getMessage() . "\n");
                    $this->cliOutput->exit(-1);
                }
            }
        }
        catch (UnableToListContents $exception) {
            $this->cliOutput->write("Failed to list contents of {$bucketName}.\n");
            $this->cliOutput->write($exception->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        if ($download === true) {
            $this->cliOutput->write(
                "  (downloaded: {$downloadedCount}, skipped: {$skippedCount})\n\n"
            );
        }
        else {
            $this->cliOutput->write(
                "  (would download: {$wouldDownloadCount}, skipped: {$skippedCount})\n\n"
            );
        }
    }

    private function shouldDownload(
        FilesystemOperator $localFilesystem,
        string $localPath,
        int|null $remoteSize
    ): bool {
        if ($localFilesystem->fileExists($localPath) !== true) {
            return true;
        }

        if ($remoteSize === null) {
            return true;
        }

        $localSize = $localFilesystem->fileSize($localPath);

        return $localSize !== $remoteSize;
    }

    private function resolveDownloadFlag(string $mode): bool
    {
        if ($mode === self::MODE_DRY_RUN) {
            return false;
        }

        if ($mode === self::MODE_DOWNLOAD) {
            return true;
        }

        $this->cliOutput->write(
            "Unknown mode '{$mode}'. Use '" . self::MODE_DRY_RUN . "' or '" . self::MODE_DOWNLOAD . "'.\n"
        );
        $this->cliOutput->exit(-1);
    }
}

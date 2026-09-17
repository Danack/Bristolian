<?php

declare(strict_types=1);

namespace Bristolian\CliController;

use Bristolian\Service\CliOutput\CliOutput;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToListContents;
use function Safe\set_time_limit;

/**
 * CLI helpers for Scaleway object-storage buckets used only in non-production.
 */
class BucketManagementDev
{
    public const MODE_DRY_RUN = 'dry-run';

    public const MODE_DELETE = 'delete';

    /**
     * Hard-coded -dev bucket names. Never derived from Config::isProductionEnv(),
     * so this command cannot target production buckets.
     *
     * @var list<string>
     */
    public const DEV_BUCKET_NAMES = [
        'bristolian-memes-dev',
        'bristolian-stairs-images-dev',
        'bristolian-avatar-images-dev',
        'bristolian-user-documents-dev',
        'bristolian-room-files-dev',
    ];

    public function __construct(
        private CliOutput $cliOutput
    ) {
    }

    /**
     * List or delete every file in all known -dev object-storage buckets.
     *
     * @param string $mode Either 'dry-run' (default) or 'delete'
     */
    public function clear(string $mode = self::MODE_DRY_RUN): void
    {
        set_time_limit(0);

        $delete = $this->resolveDeleteFlag($mode);

        if ($delete === true) {
            $this->cliOutput->write("Mode: delete — files will be removed from -dev buckets.\n");
        }
        else {
            $this->cliOutput->write("Mode: dry-run — listing files that would be deleted.\n");
            $this->cliOutput->write("Re-run with mode 'delete' to remove them.\n");
        }

        $this->cliOutput->write("\n");

        foreach (self::DEV_BUCKET_NAMES as $bucketName) {
            $filesystem = createDevOnlyObjectFilesystem($bucketName);
            $this->clearBucket($bucketName, $filesystem, $delete);
        }
    }

    /**
     * List or delete every file in one filesystem (used by clear() and by tests).
     */
    public function clearBucket(
        string $bucketName,
        FilesystemOperator $filesystem,
        bool $delete
    ): void {
        $this->cliOutput->write("Bucket: {$bucketName}\n");

        $fileCount = 0;

        try {
            $listing = $filesystem->listContents('', true);

            foreach ($listing as $item) {
                if ($item->isFile() !== true) {
                    continue;
                }

                $path = $item->path();
                $fileCount++;

                if ($delete === true) {
                    try {
                        $filesystem->delete($path);
                        $this->cliOutput->write("  deleted: {$path}\n");
                    }
                    catch (UnableToDeleteFile $exception) {
                        $this->cliOutput->write("  FAILED to delete: {$path}\n");
                        $this->cliOutput->write("  " . $exception->getMessage() . "\n");
                        $this->cliOutput->exit(-1);
                    }
                }
                else {
                    $this->cliOutput->write("  would delete: {$path}\n");
                }
            }
        }
        catch (UnableToListContents $exception) {
            $this->cliOutput->write("Failed to list contents of {$bucketName}.\n");
            $this->cliOutput->write($exception->getMessage() . "\n");
            $this->cliOutput->exit(-1);
        }

        $this->cliOutput->write("  ({$fileCount} file(s))\n\n");
    }

    private function resolveDeleteFlag(string $mode): bool
    {
        if ($mode === self::MODE_DRY_RUN) {
            return false;
        }

        if ($mode === self::MODE_DELETE) {
            return true;
        }

        $this->cliOutput->write(
            "Unknown mode '{$mode}'. Use '" . self::MODE_DRY_RUN . "' or '" . self::MODE_DELETE . "'.\n"
        );
        $this->cliOutput->exit(-1);
    }
}

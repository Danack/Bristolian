<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;
use Bristolian\Service\CliOutput\CliOutput;
use Bristolian\Service\MemeFileLocalCache\EnsureMemeFileCached;
use Bristolian\Service\MemeImageOcr\MemeImageOcrRunner;

class MemeOcr
{
    const MAX_MEME_TEXT_LENGTH = 4095;

    public function __construct(
        private MemeFilesystem $memeFilesystem,
        private LocalCacheFilesystem $localCacheFilesystem,
        private MemeTextRepo $memeTextRepo,
        private ProcessorRunRecordRepo $processorRunRecordRepo,
        private ProcessorRepo $processorRepo,
        private MemeStorageRepo $memeStorageRepo,
        private CliOutput $cliOutput,
        private MemeImageOcrRunner $memeImageOcrRunner,
        private EnsureMemeFileCached $ensureMemeFileCached,
    ) {
    }

    public function process(): void
    {
        // @codeCoverageIgnoreStart
        $callable = function () {
            $this->runInternal();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 5,
            $sleepTime = 1,
            $maxRunTime = 600
        );
        // @codeCoverageIgnoreEnd
    }

    public function runInternal(): void
    {
        if ($this->processorRepo->getProcessorEnabled(ProcessType::meme_ocr) !== true) {
            $this->cliOutput->write("ProcessType::meme_ocr is not enabled.\n");
            return;
        }

        $run_id = $this->processorRunRecordRepo->startRun(ProcessType::meme_ocr);

        $next_meme = $this->memeTextRepo->getNextMemeToOCR();

        if ($next_meme === null) {
            $this->cliOutput->write("No memes need text generating.\n");
            $debug_info = "No memes need text generating.";
            goto finish;
        }

        $cacheResult = $this->ensureMemeFileCached->ensureMemeFileCached(
            $this->localCacheFilesystem,
            $this->memeFilesystem,
            $next_meme->normalized_name,
            $next_meme->id,
            $this->memeStorageRepo,
            $this->cliOutput
        );
        if (!$cacheResult->succeeded) {
            $debug_info = $cacheResult->failureDebugInfo ?? 'Cache failed';
            goto finish;
        }

        $localCacheFilename = sprintf(
            "%s/%s",
            $this->localCacheFilesystem->getFullPath(),
            $next_meme->normalized_name
        );
        $filenameToServe = realpath($localCacheFilename);

        try {
            $found_text = $this->memeImageOcrRunner->extractTextFromImageFile(
                $filenameToServe !== false ? $filenameToServe : $localCacheFilename
            );

            if (strlen($found_text) >= self::MAX_MEME_TEXT_LENGTH) {
                $found_text = substr($found_text, 0, self::MAX_MEME_TEXT_LENGTH - 1);
            }

            $this->cliOutput->write("OCRed meme text: " . $found_text . "\n");

            $this->memeTextRepo->saveMemeText(
                $next_meme,
                $found_text
            );

            $debug_info = "Processed meme: " . $next_meme->normalized_name;
        }
        catch (\Exception $exception) {
            $this->cliOutput->write("Failed to process OCR:\n");
            $this->cliOutput->write("  " . $exception->getMessage());
            $this->cliOutput->write("\n");
            $previous = $exception->getPrevious();

            if ($previous !== null) {
                // @codeCoverageIgnoreStart
                $this->cliOutput->write("Previous: \n" . $previous->getMessage());
                $this->cliOutput->write("\n");
                // @codeCoverageIgnoreEnd
            }
            $this->cliOutput->write("\n");
            $debug_info = "Failed to process OCR: " . $exception->getMessage();
            goto finish;
        }

        finish:
        $this->processorRunRecordRepo->setRunFinished($run_id, $debug_info);
    }
}

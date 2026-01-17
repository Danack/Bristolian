<?php

declare(strict_types = 1);

namespace Bristolian\CliController;

use Bristolian\Filesystem\LocalCacheFilesystem;
use Bristolian\Filesystem\MemeFilesystem;
use Bristolian\Repo\MemeStorageRepo\MemeStorageRepo;
use Bristolian\Repo\MemeTextRepo\MemeTextRepo;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use Bristolian\Repo\ProcessorRepo\ProcessorRepo;
use Bristolian\Repo\ProcessorRunRecordRepo\ProcessorRunRecordRepo;

class MemeOcr
{
    const MAX_MEME_TEXT_LENGTH = 4095;

    public function __construct(
        private MemeFilesystem $memeFilesystem,
        private LocalCacheFilesystem $localCacheFilesystem,
        private MemeTextRepo $memeTextRepo,
        private ProcessorRunRecordRepo $processorRunRecordRepo,
        private ProcessorRepo $processorRepo,
        private MemeStorageRepo $memeStorageRepo
    ) {
    }

    public function process(): void
    {
        $callable = function () {
            $this->runInternal();
        };

        continuallyExecuteCallable(
            $callable,
            $secondsBetweenRuns = 5,
            $sleepTime = 1,
            $maxRunTime = 600
        );
    }

    public function runInternal(): void
    {
        if ($this->processorRepo->getProcessorEnabled(ProcessType::meme_ocr) !== true) {
            echo "ProcessType::meme_ocr is not enabled.\n";
            return;
        }

        $run_id = $this->processorRunRecordRepo->startRun(ProcessType::meme_ocr);

        // find next image to process in database
        $next_meme = $this->memeTextRepo->getNextMemeToOCR();

        if ($next_meme === null) {
            echo "No memes need text generating.\n";
            $debug_info = "No memes need text generating.";
            goto finish;
        }


        try {
            ensureFileCachedFromStream(
                $this->localCacheFilesystem,
                $this->memeFilesystem,
                $next_meme->normalized_name
            );
        }
        catch (\League\Flysystem\UnableToReadFile $unableToReadFile) {
            echo "Failed to download file:\n";
            echo "  " . $unableToReadFile->getMessage();
            echo "\n";
            $previous = $unableToReadFile->getPrevious();


            if ($previous !== null) {
                if ($previous instanceof \Aws\S3\Exception\S3Exception) {
                    if ($previous->getStatusCode() === 404) {
                        echo "Meme file not found in storage, marking as deleted.\n";
                        $this->memeStorageRepo->markAsDeleted($next_meme->id);
                    }
                }
                else {
                    echo "Previous type: " . get_class($previous) . "\n";
                    echo "Previous: \n" . $previous->getMessage();
                    echo "\n";
                }
            }
            echo "\n";
            $debug_info = "Failed to download file: " . $unableToReadFile->getMessage();
            goto finish;
        }


        // download the image to a temp location

        $localCacheFilename = sprintf(
            "%s/%s",
            $this->localCacheFilesystem->getFullPath(),
            $next_meme->normalized_name
        );
        $filenameToServe = realpath($localCacheFilename);

        try {
            // run the OCR,
            $found_text = $this->run_the_ocr($filenameToServe);

            if (strlen($found_text) >= self::MAX_MEME_TEXT_LENGTH) {
                $found_text = substr($found_text, 0, self::MAX_MEME_TEXT_LENGTH - 1);
            }

            echo "OCRed meme text: " . $found_text . "\n";

            $this->memeTextRepo->saveMemeText(
                $next_meme,
                $found_text
            );

            $debug_info = "Processed meme: " . $next_meme->normalized_name;
        }
        catch (\Exception $exception) {
            echo "Failed to process OCR:\n";
            echo "  " . $exception->getMessage();
            echo "\n";
            $previous = $exception->getPrevious();

            if ($previous !== null) {
                echo "Previous: \n" . $previous->getMessage();
                echo "\n";
            }
            echo "\n";
            $debug_info = "Failed to process OCR: " . $exception->getMessage();
            goto finish;
        }

finish:
        $this->processorRunRecordRepo->setRunFinished($run_id, $debug_info);
    }


    private function run_the_ocr(string $imageFile): string
    {
        $pythonScript = '/var/app/containers/supervisord/image_ocr.py';

        $cmd = [
            '/usr/bin/env',
            'python3',
            $pythonScript,
            $imageFile,
        ];

        $descriptorspec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start OCR process');
        }

// We are not sending anything to stdin
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                "OCR process failed (exit code {$exitCode}): {$stderr}"
            );
        }

// At this point $stdout contains the JSON output
        $data = json_decode($stdout, true, flags: JSON_THROW_ON_ERROR);

        // $data now holds:
        // [
        //   'source_file' => ...,
        //   'text' => [...],
        //   'text_joined' => "..."
        // ]

        return $data['text_joined'];
    }
}
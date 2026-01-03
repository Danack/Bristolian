<?php

namespace Bristolian\CliController;

use Bristolian\Repo\MemeTextRepo\MemeTextRepo;



class MemeOcr
{
    public function __construct(
        private MemeTextRepo $memeTextRepo
    ) {

    }

    public function process(): void
    {
        echo "Woot would have run.";

        // find next image to process in database
        $next_meme = $this->memeTextRepo->getNextMemeToOCR();

        if ($next_meme === null) {
            return;
        }

//        $next_meme->normalized_name

        // download the image to a temp location

        // run the OCR,

        // If it worked, save the text.

        // If if failed, increment some attempts marker.
    }


    private function run_the_ocr(): string
    {
        $pythonScript = '/path/to/ocr.py';
        $imageFile = '/path/to/image.jpg';

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
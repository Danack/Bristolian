<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeImageOcr;

/**
 * Production: runs containers/supervisord/image_ocr.py via python3.
 * @codeCoverageIgnore
 */
final class ProcOpenPythonMemeImageOcrRunner implements MemeImageOcrRunner
{
    private const DEFAULT_PYTHON_SCRIPT = '/var/app/containers/supervisord/image_ocr.py';

    public function __construct(
        private string $pythonScriptPath = self::DEFAULT_PYTHON_SCRIPT
    ) {
    }

    public function extractTextFromImageFile(string $absoluteImagePath): string
    {
        $cmd = [
            '/usr/bin/env',
            'python3',
            $this->pythonScriptPath,
            $absoluteImagePath,
        ];

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to start OCR process');
        }

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

        $data = json_decode((string) $stdout, true, flags: JSON_THROW_ON_ERROR);

        return $data['text_joined'];
    }
}

<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeImageOcr;

/**
 * Test double: fixed text or throws.
 */
final class FakeMemeImageOcrRunner implements MemeImageOcrRunner
{
    public function __construct(
        private string $textJoined = 'fake ocr text',
        private ?\Throwable $throwable = null
    ) {
    }

    public function extractTextFromImageFile(string $absoluteImagePath): string
    {
        if ($this->throwable !== null) {
            throw $this->throwable;
        }

        return $this->textJoined;
    }
}

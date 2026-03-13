<?php

declare(strict_types=1);

namespace Bristolian\Service\MemeImageOcr;

/**
 * Runs OCR on a local image file and returns joined text (same contract as image_ocr.py JSON).
 */
interface MemeImageOcrRunner
{
    /**
     * @throws \RuntimeException When the OCR process cannot start, exits non-zero, or JSON is invalid
     * @throws \JsonException When stdout is not valid JSON
     */
    public function extractTextFromImageFile(string $absoluteImagePath): string;
}

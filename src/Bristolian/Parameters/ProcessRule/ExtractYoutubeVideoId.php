<?php

declare(strict_types=1);

namespace Bristolian\Parameters\ProcessRule;

use DataType\DataStorage\DataStorage;
use DataType\OpenApi\ParamDescription;
use DataType\ProcessedValues;
use DataType\ProcessRule\ProcessRule;
use DataType\ValidationResult;

/**
 * Extracts and normalises a YouTube video ID from a URL or raw ID.
 * Uses extract_youtube_video_id(); fails validation if extraction returns null.
 */
class ExtractYoutubeVideoId implements ProcessRule
{
    public const ERROR_INVALID_YOUTUBE_URL = 'Invalid YouTube URL';

    public function process(
        mixed $value,
        ProcessedValues $processedValues,
        DataStorage $inputStorage
    ): ValidationResult {
        $url = trim((string) $value);
        if ($url === '') {
            return ValidationResult::errorResult($inputStorage, self::ERROR_INVALID_YOUTUBE_URL);
        }

        $videoId = extract_youtube_video_id($url);
        if ($videoId === null) {
            return ValidationResult::errorResult($inputStorage, self::ERROR_INVALID_YOUTUBE_URL);
        }

        return ValidationResult::valueResult($videoId);
    }

    public function updateParamDescription(ParamDescription $paramDescription): void
    {
        $paramDescription->setDescription('YouTube video URL or 11-character video ID');
    }
}

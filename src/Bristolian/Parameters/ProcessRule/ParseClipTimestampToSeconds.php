<?php

declare(strict_types=1);

namespace Bristolian\Parameters\ProcessRule;

use DataType\DataStorage\DataStorage;
use DataType\OpenApi\ParamDescription;
use DataType\ProcessedValues;
use DataType\ProcessRule\ProcessRule;
use DataType\ValidationResult;

/**
 * Converts a clip time string (seconds, M:SS, H:MM:SS) to integer seconds via {@see parse_clip_timestamp_to_seconds()}.
 */
class ParseClipTimestampToSeconds implements ProcessRule
{
    public const ERROR_INVALID_TIMESTAMP = 'Invalid clip timestamp';

    public const DESCRIPTION_TEXT = 'Clip time: seconds, M:SS, or H:MM:SS (max 10 hours)';

    public function process(
        mixed $value,
        ProcessedValues $processedValues,
        DataStorage $inputStorage
    ): ValidationResult {
        $seconds = parse_clip_timestamp_to_seconds((string) $value);
        if ($seconds === null) {
            return ValidationResult::errorResult($inputStorage, self::ERROR_INVALID_TIMESTAMP);
        }

        return ValidationResult::valueResult($seconds);
    }

    public function updateParamDescription(ParamDescription $paramDescription): void
    {
        $paramDescription->setDescription(self::DESCRIPTION_TEXT);
    }
}

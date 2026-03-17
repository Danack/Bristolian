<?php

declare(strict_types=1);

namespace Bristolian\Parameters\ProcessRule;

use DataType\DataStorage\DataStorage;
use DataType\OpenApi\ParamDescription;
use DataType\ProcessedValues;
use DataType\ProcessRule\ProcessRule;
use DataType\ValidationResult;

/**
 * Converts optional comma-separated tag_ids string to array of trimmed non-empty tag ids.
 * Null or empty string becomes []. Rejects empty tags and more than MAX_TAGS.
 */
class OptionalStringToRoomSearchTagIds implements ProcessRule
{
    public const MAX_TAGS = 5;

    public const ERROR_EMPTY_TAG = 'Tag ids cannot contain empty values';

    public const ERROR_TOO_MANY_TAGS = 'Maximum ' . self::MAX_TAGS . ' tags allowed';

    public function process(
        mixed $value,
        ProcessedValues $processedValues,
        DataStorage $inputStorage
    ): ValidationResult {
        if ($value === null || $value === '') {
            return ValidationResult::valueResult([]);
        }

        $trimmedParts = array_map('trim', explode(',', (string) $value));
        $tagIds = [];
        foreach ($trimmedParts as $part) {
            if ($part === '') {
                return ValidationResult::errorResult($inputStorage, self::ERROR_EMPTY_TAG);
            }
            $tagIds[] = $part;
        }

        if (count($tagIds) > self::MAX_TAGS) {
            return ValidationResult::errorResult($inputStorage, self::ERROR_TOO_MANY_TAGS);
        }

        return ValidationResult::valueResult($tagIds);
    }

    public function updateParamDescription(ParamDescription $paramDescription): void
    {
        $paramDescription->setType('array');
        $paramDescription->setDescription('Comma-separated tag ids, max ' . self::MAX_TAGS);
    }
}

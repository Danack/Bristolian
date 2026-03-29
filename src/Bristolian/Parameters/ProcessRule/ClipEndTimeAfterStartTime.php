<?php

declare(strict_types=1);

namespace Bristolian\Parameters\ProcessRule;

use DataType\DataStorage\DataStorage;
use DataType\Messages;
use DataType\OpenApi\ParamDescription;
use DataType\ProcessedValues;
use DataType\ProcessRule\ProcessRule;
use DataType\ValidationResult;
use DataType\Exception\DataTypeLogicException;

/**
 * Ensures the end clip time (seconds) is strictly greater than another input already processed
 * (typically {@see ClipTimestamp} for {@code start_time}).
 */
class ClipEndTimeAfterStartTime implements ProcessRule
{
    public const ERROR_END_NOT_AFTER_START = 'End time must be after start time.';

    public const ERROR_START_TIME_MUST_BE_INT = 'End time must be after start time.';

    public const DESCRIPTION_TEXT = 'Must be after start time';

    public function __construct(
        private string $startTimeInputName
    ) {
    }

    public function process(
        mixed $value,
        ProcessedValues $processedValues,
        DataStorage $inputStorage
    ): ValidationResult {
        if ($processedValues->hasValue($this->startTimeInputName) !== true) {
            return ValidationResult::errorResult(
                $inputStorage,
                sprintf(Messages::ERROR_NO_PREVIOUS_PARAMETER, $this->startTimeInputName)
            );
        }

        $start_seconds = $processedValues->getValue($this->startTimeInputName);
        if (is_int($value) !== true) {
            throw new DataTypeLogicException("end value must be integer");
        }

        if (is_int($start_seconds) !== true) {
            throw new DataTypeLogicException("start value must be integer");
        }

        if ($value <= $start_seconds) {
            return ValidationResult::errorResult($inputStorage, self::ERROR_END_NOT_AFTER_START);
        }

        return ValidationResult::valueResult($value);
    }

    public function updateParamDescription(ParamDescription $paramDescription): void
    {
        $paramDescription->setDescription(self::DESCRIPTION_TEXT);
    }
}

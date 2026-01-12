<?php

declare(strict_types=1);

namespace Bristolian\Parameters\ProcessRule;

use DataType\DataStorage\DataStorage;
use DataType\Messages;
use DataType\OpenApi\ParamDescription;
use DataType\ProcessedValues;
use DataType\ProcessRule\ProcessRule;
use DataType\ValidationResult;

/**
 * Converts a string value to boolean, defaulting to true if not provided.
 * Accepts "true", "1", "yes" as true values.
 * Accepts "false", "0", "no" as false values.
 */
class StringToBoolDefaultTrue implements ProcessRule
{
    public function process(
        mixed $value,
        ProcessedValues $processedValues,
        DataStorage $inputStorage
    ): ValidationResult {
        // If null or empty, default to true
        if ($value === null || $value === '') {
            return ValidationResult::valueResult(true);
        }

        $stringValue = strtolower(trim((string)$value));

        if (in_array($stringValue, ['true', '1', 'yes'], true)) {
            return ValidationResult::valueResult(true);
        }

        if (in_array($stringValue, ['false', '0', 'no'], true)) {
            return ValidationResult::valueResult(false);
        }

        // Invalid value, return error
        return ValidationResult::errorResult(
            $inputStorage,
            "Value must be a boolean (true/false, 1/0, yes/no)"
        );
    }

    public function updateParamDescription(ParamDescription $paramDescription): void
    {
        $paramDescription->setType('boolean');
        $paramDescription->setDefault(true);
    }
}

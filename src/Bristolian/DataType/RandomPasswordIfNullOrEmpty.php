<?php

declare(strict_types = 1);

namespace Bristolian\DataType;

use DataType\DataStorage\DataStorage;
use DataType\OpenApi\ParamDescription;
use DataType\ProcessedValues;
use DataType\ProcessRule\ProcessRule;
use DataType\ValidationResult;

class RandomPasswordIfNullOrEmpty implements ProcessRule
{
    /** @var int  */
    private $passwordLength;

    /**
     *
     * @param int $passwordLength The length of the password to generate if string
     * is empty
     */
    public function __construct(int $passwordLength)
    {
        $this->passwordLength = $passwordLength;
    }

    public function process(
        $value,
        ProcessedValues $processedValues,
        DataStorage $inputStorage
    ): ValidationResult {
        if ($value === null) {
            return $this->generatePassword();
        }

        if (is_string($value) === true && strlen($value) === 0) {
            return $this->generatePassword();
        }

        return ValidationResult::valueResult($value);
    }

    private function generatePassword()
    {
        $generated_string = bin2hex(random_bytes($this->passwordLength));

        return ValidationResult::valueResult($generated_string);
    }

    public function updateParamDescription(ParamDescription $paramDescription): void
    {
        // Nothing to do
    }
}
